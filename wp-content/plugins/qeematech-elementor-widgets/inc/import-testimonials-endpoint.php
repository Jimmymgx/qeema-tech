<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reconciles the `testimonial` CPT against the live site's real 31 video
 * testimonials — NOT a fresh import. The 30 posts already on this site are
 * in the exact same shape the old site itself uses (bare numeric titles, no
 * quote text — the old site genuinely has no client names or written
 * quotes anywhere, confirmed via both REST and rendered HTML), meaning this
 * content was already migrated once. This importer scrapes the old site's
 * `/آراء-العملاء/` page for all 31 (poster image, video URL) pairs, matches
 * each against what's already here by filename, and creates ONLY the
 * genuinely-missing one(s) — the existing 30 are never touched, matching
 * the same "fill gaps, don't overwrite" rule used for the portfolio import.
 */

function qeema_import_testimonials_option_key() {
	return 'qeema_testimonials_import_progress';
}

function qeema_import_testimonials_get_progress() {
	$default = array(
		'items'        => null, // scraped list: [{old_id, poster_url, video_url}, ...]
		'existing_mp4' => null, // normalized basenames already present locally
		'existing_url' => null, // exact youtube/facebook URLs already present locally
		'current_page' => 1,
		'total_pages'  => null,
		'created'      => 0,
		'skipped'      => 0,
		'errors'       => array(),
		'log'          => array(),
		'done'         => false,
	);
	$stored = get_option( qeema_import_testimonials_option_key(), array() );
	return wp_parse_args( $stored, $default );
}

function qeema_import_testimonials_save_progress( $progress ) {
	if ( count( $progress['log'] ) > 200 ) {
		$progress['log'] = array_slice( $progress['log'], -200 );
	}
	if ( count( $progress['errors'] ) > 200 ) {
		$progress['errors'] = array_slice( $progress['errors'], -200 );
	}
	update_option( qeema_import_testimonials_option_key(), $progress, false );
}

/**
 * A local mp4 filename that came from a WordPress upload-collision rename
 * (e.g. "...FPnlOCg-1.mp4" next to an existing "...FPnlOCg.mp4") is still
 * the same source video — strip a trailing "-<digits>" right before the
 * extension so both sides of the comparison normalize to the same key.
 */
function qeema_import_normalize_video_basename( $url_or_filename ) {
	$path = wp_parse_url( $url_or_filename, PHP_URL_PATH );
	$name = wp_basename( $path ? $path : $url_or_filename );
	$name = preg_replace( '/-\d+(\.[a-zA-Z0-9]+)$/', '$1', $name );
	return $name;
}

/**
 * Parses the testimonials page into an ordered list of (old post id, poster
 * image url, video url) — poster comes from the per-item inline <style>
 * block's background-image, video from the one real content href inside
 * each block (an .mp4 file, or a youtube.com/youtu.be link, or in one case
 * a facebook.com/share/v/ link — generic facebook.com company-page links
 * elsewhere in the block's shared header/footer markup are deliberately
 * excluded by requiring the video/share/watch-shaped path).
 */
function qeema_import_scrape_testimonials_page( $html ) {
	$items = array();
	if ( empty( $html ) ) {
		return $items;
	}

	// The per-item poster <style id="loop-dynamic-..."> block is emitted
	// BEFORE that item's own loop-item wrapper marker (confirmed by direct
	// inspection — not inside it), so it can't be captured from the
	// chunk-sliced pass below. A separate whole-document pass keyed by the
	// same numeric id works regardless of where each style block falls.
	$posters = array();
	preg_match_all( '/\.e-loop-item-(\d+)\s[^{]*\{background-image:url\("([^"]+)"\)/', $html, $poster_matches );
	foreach ( $poster_matches[1] as $i => $poster_id ) {
		$posters[ (int) $poster_id ] = html_entity_decode( $poster_matches[2][ $i ], ENT_QUOTES );
	}

	preg_match_all( '/data-elementor-type="loop-item" data-elementor-id="\d+" class="elementor elementor-\d+ e-loop-item e-loop-item-(\d+)/', $html, $matches, PREG_OFFSET_CAPTURE );

	$count = count( $matches[1] );
	for ( $i = 0; $i < $count; $i++ ) {
		$id    = (int) $matches[1][ $i ][0];
		$start = $matches[1][ $i ][1];
		$end   = ( $i + 1 < $count ) ? $matches[1][ $i + 1 ][1] : strlen( $html );
		$chunk = substr( $html, $start, $end - $start );

		$poster_url = isset( $posters[ $id ] ) ? $posters[ $id ] : '';

		$video_url = '';
		if ( preg_match( '/href="(https?:\/\/[^"]*\.mp4[^"]*)"/i', $chunk, $m ) ) {
			$video_url = html_entity_decode( $m[1], ENT_QUOTES );
		} elseif ( preg_match( '/href="(https?:\/\/(?:www\.)?youtu(?:\.be|be\.com)\/[^"]+)"/i', $chunk, $m ) ) {
			$video_url = html_entity_decode( $m[1], ENT_QUOTES );
		} elseif ( preg_match( '/href="(https?:\/\/(?:www\.)?facebook\.com\/share\/v\/[^"]+)"/i', $chunk, $m ) ) {
			$video_url = html_entity_decode( $m[1], ENT_QUOTES );
		}

		if ( $poster_url || $video_url ) {
			$items[] = array(
				'old_id'     => $id,
				'poster_url' => $poster_url,
				'video_url'  => $video_url,
			);
		}
	}

	return $items;
}

/**
 * Builds the two "already have this" lookup sets from the 30 existing
 * testimonial posts' video_link_ meta — one for local mp4 files (normalized
 * basename), one for exact external URLs (YouTube/Facebook).
 */
function qeema_import_testimonials_load_existing() {
	$mp4_basenames = array();
	$exact_urls    = array();

	$posts = get_posts( array(
		'post_type'      => 'testimonial',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );

	foreach ( $posts as $post_id ) {
		$video = get_post_meta( $post_id, 'video_link_', true );
		if ( ! $video ) {
			continue;
		}
		if ( false !== stripos( $video, '.mp4' ) ) {
			$mp4_basenames[ qeema_import_normalize_video_basename( $video ) ] = true;
		} else {
			$exact_urls[ $video ] = true;
		}
	}

	return array( 'mp4' => $mp4_basenames, 'urls' => $exact_urls );
}

function qeema_import_testimonials_next_title_number() {
	global $wpdb;
	$max = $wpdb->get_var( "SELECT MAX(CAST(post_title AS UNSIGNED)) FROM {$wpdb->posts} WHERE post_type = 'testimonial' AND post_title REGEXP '^[0-9]+$'" );
	return $max ? ( (int) $max + 1 ) : 1;
}

function qeema_import_testimonials_batch() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}

	$progress = qeema_import_testimonials_get_progress();

	if ( true === $progress['done'] ) {
		wp_send_json_success( $progress );
	}

	if ( null === $progress['items'] ) {
		$html = qeema_import_remote_get_html( QEEMA_IMPORT_OLD_SITE . '/%d8%a2%d8%b1%d8%a7%d8%a1-%d8%a7%d9%84%d8%b9%d9%85%d9%84%d8%a7%d8%a1/' );
		if ( empty( $html ) ) {
			$progress['errors'][] = 'Failed to fetch testimonials page from live site (will retry on next call).';
			qeema_import_testimonials_save_progress( $progress );
			wp_send_json_success( $progress );
		}
		$progress['items'] = qeema_import_scrape_testimonials_page( $html );
	}

	if ( null === $progress['existing_mp4'] ) {
		$existing                    = qeema_import_testimonials_load_existing();
		$progress['existing_mp4']    = $existing['mp4'];
		$progress['existing_url']    = $existing['urls'];
	}

	$items = $progress['items'];
	$total = count( $items );
	if ( null === $progress['total_pages'] ) {
		$progress['total_pages'] = $total;
	}

	$index = $progress['current_page'] - 1;
	if ( $index < $total ) {
		$item = $items[ $index ];
		try {
			$already_have = false;
			if ( $item['video_url'] ) {
				if ( false !== stripos( $item['video_url'], '.mp4' ) ) {
					$already_have = isset( $progress['existing_mp4'][ qeema_import_normalize_video_basename( $item['video_url'] ) ] );
				} else {
					$already_have = isset( $progress['existing_url'][ $item['video_url'] ] );
				}
			}

			if ( ! $item['video_url'] || $already_have ) {
				$progress['skipped']++;
				$progress['log'][] = $already_have
					? "skip (already have): old id {$item['old_id']}"
					: "skip (no video found): old id {$item['old_id']}";
			} else {
				$title   = (string) qeema_import_testimonials_next_title_number();
				$new_id  = wp_insert_post( array(
					'post_type'      => 'testimonial',
					'post_status'    => 'publish',
					'post_title'     => $title,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				), true );

				if ( is_wp_error( $new_id ) || ! $new_id ) {
					$progress['errors'][] = "failed to create testimonial for old id {$item['old_id']}";
				} else {
					if ( $item['poster_url'] ) {
						$image_id = qeema_import_sideload_image( $item['poster_url'], $new_id, 'Testimonial ' . $title );
						if ( $image_id ) {
							update_post_meta( $new_id, 'client_image', $image_id );
							set_post_thumbnail( $new_id, $image_id );
						}
					}

					if ( false !== stripos( $item['video_url'], '.mp4' ) ) {
						$video_id = qeema_import_sideload_file( $item['video_url'], $new_id, 'Testimonial ' . $title . ' video' );
						if ( $video_id ) {
							update_post_meta( $new_id, 'video_link_', wp_get_attachment_url( $video_id ) );
							$progress['existing_mp4'][ qeema_import_normalize_video_basename( $item['video_url'] ) ] = true;
						} else {
							$progress['errors'][] = "video download failed for old id {$item['old_id']}, post created without video";
						}
					} else {
						update_post_meta( $new_id, 'video_link_', $item['video_url'] );
						$progress['existing_url'][ $item['video_url'] ] = true;
					}

					$progress['created']++;
					$progress['log'][] = "created: old id {$item['old_id']} -> new testimonial #{$title}";
				}
			}
		} catch ( \Throwable $e ) {
			$progress['errors'][] = "exception on old id {$item['old_id']}: " . $e->getMessage();
		}

		$progress['current_page']++;
	}

	if ( $progress['current_page'] > $total ) {
		$progress['done'] = true;
	}

	qeema_import_testimonials_save_progress( $progress );
	wp_send_json_success( $progress );
}
add_action( 'wp_ajax_qeema_import_testimonials_batch', 'qeema_import_testimonials_batch' );

function qeema_import_testimonials_status() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	wp_send_json_success( qeema_import_testimonials_get_progress() );
}
add_action( 'wp_ajax_qeema_import_testimonials_status', 'qeema_import_testimonials_status' );

function qeema_import_testimonials_reset() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	delete_option( qeema_import_testimonials_option_key() );
	wp_send_json_success( qeema_import_testimonials_get_progress() );
}
add_action( 'wp_ajax_qeema_import_testimonials_reset', 'qeema_import_testimonials_reset' );
