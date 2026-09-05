<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Batch importer: pulls every blog post from the live site (qeematech.net)
 * into this site's `post` type. Unlike the portfolio importer, no HTML
 * scraping is needed here — the old site's REST API fully exposes
 * `content.rendered`, `excerpt.rendered`, author, categories, tags, and
 * featured image for every post, confirmed during this project's research
 * pass. Walks the old site's REST collection page-by-page, one page per
 * batch, same resumable-via-page-pointer shape as the portfolio importer.
 *
 * The old site's post-sitemap has ~1,385 URLs, the large majority of which
 * are programmatic/templated SEO doorway pages (e.g. "تصميم متجر [product]"
 * repeated across dozens of product niches) rather than editorial articles.
 * The user explicitly chose to import all of them anyway, for full URL/SEO
 * parity ahead of the DNS cutover — this file does not filter by content
 * quality.
 *
 * The ~11 blog posts already migrated to this site are matched by slug and
 * skipped entirely (never touched), matching the same "don't clobber
 * existing content" rule applied to the portfolio importer.
 */

function qeema_import_blog_option_key() {
	return 'qeema_blog_import_progress';
}

function qeema_import_blog_get_progress() {
	$default = array(
		'current_page' => 1,
		'total_pages'  => null,
		'default_author_id' => null,
		'created'      => 0,
		'skipped'      => 0,
		'errors'       => array(),
		'log'          => array(),
		'done'         => false,
	);
	$stored = get_option( qeema_import_blog_option_key(), array() );
	return wp_parse_args( $stored, $default );
}

function qeema_import_blog_save_progress( $progress ) {
	if ( count( $progress['log'] ) > 200 ) {
		$progress['log'] = array_slice( $progress['log'], -200 );
	}
	if ( count( $progress['errors'] ) > 200 ) {
		$progress['errors'] = array_slice( $progress['errors'], -200 );
	}
	update_option( qeema_import_blog_option_key(), $progress, false );
}

/**
 * Old-site posts have real byline names ("Abdulazeim", "Qeema", etc.) that
 * don't exist as WP users here — rather than inventing user accounts,
 * every imported post is attributed to this site's own first administrator
 * account.
 */
function qeema_import_blog_default_author() {
	$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
	if ( ! empty( $admins ) ) {
		return (int) $admins[0]->ID;
	}
	return 1;
}

/**
 * Rewrites internal links inside scraped/imported content so migrated
 * posts don't reintroduce dead links: absolute old-domain URLs become
 * relative, and the three slugs already renamed earlier in this project
 * (about-us/services/contact-us) are updated to their real new-site slugs.
 */
function qeema_import_rewrite_internal_links( $html ) {
	if ( '' === (string) $html ) {
		return $html;
	}
	$html = str_replace(
		array( 'https://www.qeematech.net', 'https://qeematech.net', 'http://www.qeematech.net', 'http://qeematech.net' ),
		'',
		$html
	);
	$html = str_replace( '/about-us/', '/من-نحن/', $html );
	$html = str_replace( '/services/', '/خدماتنا/', $html );
	$html = str_replace( '/اتصل-بنا/', '/أتصل-بنا/', $html );
	$html = str_replace( '/contact-us/', '/أتصل-بنا/', $html );
	return $html;
}

function qeema_import_blog_find_existing( $slug ) {
	$posts = get_posts( array(
		'post_type'      => 'post',
		'name'           => $slug,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	return ! empty( $posts ) ? (int) $posts[0] : 0;
}

/**
 * Finds an existing term by name in the given taxonomy, or creates one —
 * matches the old site's real category/tag names rather than inventing a
 * different taxonomy shape.
 */
function qeema_import_blog_find_or_create_term( $name, $taxonomy ) {
	$name = trim( wp_strip_all_tags( $name ) );
	if ( '' === $name ) {
		return 0;
	}
	$term = get_term_by( 'name', $name, $taxonomy );
	if ( $term && ! is_wp_error( $term ) ) {
		return (int) $term->term_id;
	}
	$created = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $created ) ) {
		return 0;
	}
	return (int) $created['term_id'];
}

function qeema_import_blog_batch() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}

	$progress = qeema_import_blog_get_progress();

	if ( true === $progress['done'] ) {
		wp_send_json_success( $progress );
	}

	if ( null === $progress['default_author_id'] ) {
		$progress['default_author_id'] = qeema_import_blog_default_author();
	}

	// Smaller than the collection's own default page size for the same
	// reason as the portfolio importer: each post can trigger a real image
	// sideload against the live production site, and a big batch risks
	// tripping PHP's max_execution_time (confirmed happening at batch size
	// 5 for the heavier portfolio importer).
	$batch_size = 5;
	$list_url   = QEEMA_IMPORT_OLD_SITE . '/wp-json/wp/v2/posts?per_page=' . $batch_size . '&page=' . $progress['current_page'] . '&_embed=1';
	$fetched    = qeema_import_remote_get_json( $list_url );

	if ( ! $fetched ) {
		// Transient failure, not completion — see the matching comment in
		// qeema_import_portfolio_batch(). Leave current_page and done alone
		// so the next call just retries this same page.
		$progress['errors'][] = 'Page ' . $progress['current_page'] . ': failed to fetch post list from live site (will retry on next call).';
		qeema_import_blog_save_progress( $progress );
		wp_send_json_success( $progress );
	}

	if ( null === $progress['total_pages'] ) {
		$total_pages_header = $fetched['headers']['x-wp-totalpages'];
		$progress['total_pages'] = $total_pages_header ? (int) $total_pages_header : 1;
	}

	$items = is_array( $fetched['body'] ) ? $fetched['body'] : array();

	foreach ( $items as $item ) {
		$slug = isset( $item['slug'] ) ? $item['slug'] : '';
		if ( '' === $slug ) {
			continue;
		}

		try {
			if ( qeema_import_blog_find_existing( $slug ) ) {
				$progress['skipped']++;
				$progress['log'][] = "skip (already migrated): {$slug}";
				continue;
			}

			$new_id = qeema_import_blog_create( $item, $progress['default_author_id'] );
			if ( $new_id ) {
				$progress['created']++;
				$progress['log'][] = "created: {$slug}";
			} else {
				$progress['errors'][] = "failed to create: {$slug}";
			}
		} catch ( \Throwable $e ) {
			$progress['errors'][] = "exception on {$slug}: " . $e->getMessage();
		}
	}

	$progress['current_page']++;
	if ( $progress['current_page'] > $progress['total_pages'] ) {
		$progress['done'] = true;
	}

	qeema_import_blog_save_progress( $progress );
	wp_send_json_success( $progress );
}
add_action( 'wp_ajax_qeema_import_blog_batch', 'qeema_import_blog_batch' );

function qeema_import_blog_create( $item, $default_author_id ) {
	$slug    = $item['slug'];
	$title   = isset( $item['title']['rendered'] ) ? wp_strip_all_tags( $item['title']['rendered'] ) : $slug;
	$content = isset( $item['content']['rendered'] ) ? qeema_import_rewrite_internal_links( $item['content']['rendered'] ) : '';
	$excerpt = isset( $item['excerpt']['rendered'] ) ? wp_strip_all_tags( $item['excerpt']['rendered'] ) : '';

	$post_id = wp_insert_post( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'post_title'     => $title,
		'post_name'      => $slug,
		'post_content'   => $content,
		'post_excerpt'   => $excerpt,
		'post_date'      => isset( $item['date'] ) ? $item['date'] : current_time( 'mysql' ),
		'post_date_gmt'  => isset( $item['date_gmt'] ) ? $item['date_gmt'] : current_time( 'mysql', true ),
		'post_author'    => $default_author_id,
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	), true );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 0;
	}

	$category_ids = array();
	$tag_ids      = array();
	if ( ! empty( $item['_embedded']['wp:term'] ) && is_array( $item['_embedded']['wp:term'] ) ) {
		foreach ( $item['_embedded']['wp:term'] as $term_group ) {
			foreach ( (array) $term_group as $term ) {
				if ( empty( $term['taxonomy'] ) || empty( $term['name'] ) ) {
					continue;
				}
				if ( 'category' === $term['taxonomy'] ) {
					$id = qeema_import_blog_find_or_create_term( $term['name'], 'category' );
					if ( $id ) {
						$category_ids[] = $id;
					}
				} elseif ( 'post_tag' === $term['taxonomy'] ) {
					$id = qeema_import_blog_find_or_create_term( $term['name'], 'post_tag' );
					if ( $id ) {
						$tag_ids[] = $id;
					}
				}
			}
		}
	}
	if ( ! empty( $category_ids ) ) {
		wp_set_object_terms( $post_id, $category_ids, 'category' );
	}
	if ( ! empty( $tag_ids ) ) {
		wp_set_object_terms( $post_id, $tag_ids, 'post_tag' );
	}

	$image_url = '';
	if ( ! empty( $item['_embedded']['wp:featuredmedia'][0]['source_url'] ) ) {
		$image_url = $item['_embedded']['wp:featuredmedia'][0]['source_url'];
	}
	if ( $image_url ) {
		$attachment_id = qeema_import_sideload_image( $image_url, $post_id, $title );
		if ( $attachment_id ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}

	return $post_id;
}

function qeema_import_blog_status() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	wp_send_json_success( qeema_import_blog_get_progress() );
}
add_action( 'wp_ajax_qeema_import_blog_status', 'qeema_import_blog_status' );

function qeema_import_blog_reset() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	delete_option( qeema_import_blog_option_key() );
	wp_send_json_success( qeema_import_blog_get_progress() );
}
add_action( 'wp_ajax_qeema_import_blog_reset', 'qeema_import_blog_reset' );
