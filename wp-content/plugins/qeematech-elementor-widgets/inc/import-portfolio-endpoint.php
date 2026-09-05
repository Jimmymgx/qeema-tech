<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Batch importer: pulls every portfolio item from the live site
 * (qeematech.net) into this site's `portfolio` CPT.
 *
 * Runs as an admin-triggered AJAX loop (see live-import-admin-page.php)
 * rather than a single request, since ~159 remote items — each needing a
 * REST call, an HTML scrape for case-study text, and 1+ image downloads —
 * cannot finish inside one PHP execution. Walks the old site's REST
 * collection page-by-page (one page = one batch), tracking only a page
 * pointer + counters in wp_options — never the full response bodies — so
 * resuming after a closed tab or an Apache restart is just "read the last
 * page number and keep going."
 *
 * Matching key is the post_name slug, not the title — a prior partial
 * import already decoupled several portfolio slugs from their titles
 * (recorded in this project's own portfolio_mismatches.json audit), so
 * title-based matching would silently create duplicates.
 *
 * Existing posts are never overwritten: only currently-EMPTY fields get
 * filled in from freshly-scraped data (the "fill gaps only" rule the user
 * chose over a full refresh). Posts that don't exist yet are created in
 * full, importing everything the old site's REST API + rendered HTML
 * actually contains — never fabricated placeholder data for anything it
 * doesn't have.
 */

define( 'QEEMA_IMPORT_OLD_SITE', 'https://www.qeematech.net' );

function qeema_import_portfolio_option_key() {
	return 'qeema_portfolio_import_progress';
}

function qeema_import_portfolio_get_progress() {
	$default = array(
		'current_page'  => 1,
		'total_pages'   => null,
		'category_map'  => null, // old term name => new term_id
		'created'       => 0,
		'gap_filled'    => 0,
		'skipped'       => 0,
		'errors'        => array(),
		'log'           => array(),
		'done'          => false,
	);
	$stored = get_option( qeema_import_portfolio_option_key(), array() );
	return wp_parse_args( $stored, $default );
}

function qeema_import_portfolio_save_progress( $progress ) {
	// Keep the log from growing unbounded across many batches.
	if ( count( $progress['log'] ) > 200 ) {
		$progress['log'] = array_slice( $progress['log'], -200 );
	}
	if ( count( $progress['errors'] ) > 200 ) {
		$progress['errors'] = array_slice( $progress['errors'], -200 );
	}
	update_option( qeema_import_portfolio_option_key(), $progress, false );
}

/**
 * Small wrapper: GET a URL, decode JSON, return array( body, headers ) or
 * null on any failure — every caller must handle the null case rather than
 * assume the remote (production, third-party-to-us) site is always up.
 */
function qeema_import_remote_get_json( $url ) {
	$response = wp_remote_get( $url, array( 'timeout' => 25 ) );
	if ( is_wp_error( $response ) ) {
		return null;
	}
	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return null;
	}
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( null === $body ) {
		return null;
	}
	return array(
		'body'    => $body,
		'headers' => wp_remote_retrieve_headers( $response ),
	);
}

function qeema_import_remote_get_html( $url ) {
	$response = wp_remote_get( $url, array( 'timeout' => 25 ) );
	if ( is_wp_error( $response ) ) {
		return '';
	}
	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return '';
	}
	return (string) wp_remote_retrieve_body( $response );
}

/**
 * Sideloads a remote image URL into the media library, optionally attached
 * to $post_id. Returns the new attachment ID, or 0 on failure (never
 * fabricates a fallback image).
 */
function qeema_import_sideload_image( $url, $post_id = 0, $desc = '' ) {
	if ( empty( $url ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp_file = download_url( $url, 60 );
	if ( is_wp_error( $tmp_file ) ) {
		return 0;
	}

	// A handful of old-site images are pathologically large — tens of
	// thousands of pixels per side — and GD needs a raw pixel buffer sized
	// to width*height just to OPEN the file, before any resizing happens.
	// That blew past even a 2GB memory_limit in testing (one case tried to
	// allocate >1GB for a single operation) and killed the whole
	// unattended import with an uncatchable fatal. getimagesize() reads
	// only the file header, not the pixel data, so checking dimensions
	// first is cheap regardless of how large the file itself is. Skipping
	// the rare oversized image (never fabricating a placeholder) is far
	// better than losing the entire run to one bad file.
	$dimensions = @getimagesize( $tmp_file );
	$megapixels = $dimensions ? ( $dimensions[0] * $dimensions[1] ) / 1000000 : 0;
	if ( ! $dimensions || $megapixels > 30 ) {
		@unlink( $tmp_file );
		return 0;
	}

	$file_array = array(
		'name'     => wp_basename( wp_parse_url( $url, PHP_URL_PATH ) ),
		'tmp_name' => $tmp_file,
	);

	// Skip generating the extra named thumbnail/medium/large crops for
	// imported media — the original full-size image is still stored and is
	// what every widget here reads via
	// wp_get_attachment_image_url()/get_post_thumbnail_id(), just without
	// WordPress's usual set of resized copies alongside it. Reduces memory
	// pressure and speeds up a run that's already sideloading ~1,500 images.
	$skip_sizes = function () {
		return array();
	};
	add_filter( 'intermediate_image_sizes_advanced', $skip_sizes );
	$id = media_handle_sideload( $file_array, $post_id, $desc );
	remove_filter( 'intermediate_image_sizes_advanced', $skip_sizes );

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp_file );
		return 0;
	}
	return (int) $id;
}

/**
 * Sideloads a remote non-image file (used for testimonial video clips) into
 * the media library. No dimension check — that's specific to the GD memory
 * blowup images can trigger — but WordPress doesn't run any image-editor
 * processing on video mime types regardless, so this is otherwise the same
 * download+attach flow as qeema_import_sideload_image().
 */
function qeema_import_sideload_file( $url, $post_id = 0, $desc = '' ) {
	if ( empty( $url ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp_file = download_url( $url, 120 );
	if ( is_wp_error( $tmp_file ) ) {
		return 0;
	}

	$file_array = array(
		'name'     => wp_basename( wp_parse_url( $url, PHP_URL_PATH ) ),
		'tmp_name' => $tmp_file,
	);

	$id = media_handle_sideload( $file_array, $post_id, $desc );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp_file );
		return 0;
	}
	return (int) $id;
}

/**
 * Multi-byte-safe trim for the punctuation/dash characters this scraper
 * needs to strip from extracted Arabic text. PHP's own trim() takes its
 * character list as a set of individual BYTES, not characters — passing it
 * multi-byte UTF-8 characters (Arabic comma، en/em dashes) corrupted text
 * at the boundaries into invalid UTF-8, which MySQL then silently rejected
 * on write (update_post_meta() returned false with no exception, no error
 * visible anywhere in the batch's try/catch). A regex trim with the /u
 * modifier operates on whole characters instead.
 */
function qeema_import_mb_trim( $text ) {
	$text = preg_replace( '/^[\s:،,\-–—]+|[\s:،,\-–—]+$/u', '', $text );
	return null === $text ? '' : $text;
}

/**
 * Best-effort text extraction from a rendered portfolio single page. The
 * old site's REST API exposes no body content for this post type at all
 * (confirmed: `acf` is always an empty array, no `content` field), so the
 * four case-study paragraphs and any store links only exist in the
 * rendered HTML. This is heuristic string/regex extraction, not a DOM
 * template match — it can legitimately miss a paragraph on an oddly-
 * formatted page. Callers must treat an empty return value as "not found",
 * never fill it with placeholder text.
 */
function qeema_import_scrape_portfolio_page( $html, $title ) {
	$result = array(
		'idea'      => '',
		'challenge' => '',
		'execution' => '',
		'journey'   => '',
		'service'   => '',
		'android'   => '',
		'ios'       => '',
		'link'      => '',
	);

	if ( empty( $html ) ) {
		return $result;
	}

	if ( preg_match( '/href="(https?:\/\/play\.google\.com\/[^"]+)"/i', $html, $m ) ) {
		$result['android'] = html_entity_decode( $m[1], ENT_QUOTES );
	}
	if ( preg_match( '/href="(https?:\/\/apps\.apple\.com\/[^"]+)"/i', $html, $m ) ) {
		$result['ios'] = html_entity_decode( $m[1], ENT_QUOTES );
	}
	if ( preg_match( '/<a[^>]*href="([^"]+)"[^>]*>\s*مشاهدة المشروع/u', $html, $m ) ) {
		$href = html_entity_decode( $m[1], ENT_QUOTES );
		if ( false === strpos( $href, 'qeematech.net' ) && false === strpos( $href, 'play.google.com' ) && false === strpos( $href, 'apps.apple.com' ) ) {
			$result['link'] = $href;
		}
	}

	// Plain-text pass for the labeled paragraphs — strip scripts/styles,
	// tags, collapse whitespace, then locate each known Arabic heading.
	$stripped = preg_replace( '#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html );
	$text     = wp_strip_all_tags( $stripped );
	$text     = html_entity_decode( $text, ENT_QUOTES );
	$text     = preg_replace( '/\s+/u', ' ', $text );

	$headings = array(
		'service'   => 'الخدمة',
		'challenge' => 'التحدي',
		'execution' => 'آلية التنفيذ',
		'journey'   => 'رحلة العميل معنا',
	);

	foreach ( $headings as $key => $label ) {
		$pos = mb_strpos( $text, $label );
		if ( false === $pos ) {
			continue;
		}
		$chunk = mb_substr( $text, $pos + mb_strlen( $label ), 700 );
		$cut   = mb_strlen( $chunk );
		foreach ( $headings as $other_label ) {
			if ( $other_label === $label ) {
				continue;
			}
			$p = mb_strpos( $chunk, $other_label );
			if ( false !== $p && $p > 0 && $p < $cut ) {
				$cut = $p;
			}
		}
		$result[ $key ] = qeema_import_mb_trim( mb_substr( $chunk, 0, $cut ) );
	}

	// The intro paragraph ("idea") has no labeled heading on the old site —
	// it's the text between the H1 title and the "التحدي" heading.
	if ( ! empty( $title ) ) {
		$title_pos = mb_strpos( $text, $title );
		$challenge_pos = mb_strpos( $text, 'التحدي' );
		if ( false !== $title_pos && false !== $challenge_pos && $challenge_pos > $title_pos ) {
			$idea = mb_substr( $text, $title_pos + mb_strlen( $title ), $challenge_pos - ( $title_pos + mb_strlen( $title ) ) );
			$idea = qeema_import_mb_trim( $idea );
			// Guard against accidentally capturing an entire nav/menu block
			// if the title also appears in unrelated boilerplate earlier in
			// the page — a real intro paragraph is short prose, not this.
			if ( mb_strlen( $idea ) > 20 && mb_strlen( $idea ) < 1200 ) {
				$result['idea'] = $idea;
			}
		}
	}

	// Defense in depth: guarantee valid UTF-8 regardless of source. An
	// invalid-UTF8 string passed to update_post_meta() fails the underlying
	// $wpdb query silently (returns false, throws nothing) — the very bug
	// this scraper originally tripped on — so every value leaving this
	// function is sanitized here rather than trusting each caller to do it.
	foreach ( $result as $key => $value ) {
		$result[ $key ] = wp_check_invalid_utf8( $value, true );
	}

	return $result;
}

/**
 * Loads (and caches in the progress option) the old site's 7
 * portfolio-categories terms mapped to this site's matching term IDs by
 * name — both sides use the identical 7 term names, confirmed during this
 * project's earlier slug-parity audit, so this is a direct lookup, not
 * fuzzy matching.
 */
function qeema_import_portfolio_load_category_map() {
	$fetched = qeema_import_remote_get_json( QEEMA_IMPORT_OLD_SITE . '/wp-json/wp/v2/portfolio-categories?per_page=100' );
	$map     = array(); // old_term_id => new_term_id

	if ( ! $fetched || ! is_array( $fetched['body'] ) ) {
		return $map;
	}

	foreach ( $fetched['body'] as $term ) {
		$name = isset( $term['name'] ) ? $term['name'] : '';
		if ( '' === $name ) {
			continue;
		}
		$local = get_term_by( 'name', $name, 'portfolio-categories' );
		if ( $local && ! is_wp_error( $local ) ) {
			$map[ (int) $term['id'] ] = (int) $local->term_id;
		}
	}

	return $map;
}

function qeema_import_portfolio_find_existing( $slug ) {
	$posts = get_posts( array(
		'post_type'      => 'portfolio',
		'name'           => $slug,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	return ! empty( $posts ) ? (int) $posts[0] : 0;
}

/**
 * Reports which of the fields this import cares about are currently empty
 * on an existing post — the basis for the "fill gaps only, never overwrite"
 * rule.
 */
function qeema_import_portfolio_empty_fields( $post_id ) {
	$empty = array();

	if ( ! get_post_thumbnail_id( $post_id ) ) {
		$empty['thumbnail'] = true;
	}
	if ( ! absint( get_post_meta( $post_id, 'banner', true ) ) ) {
		$empty['banner'] = true;
	}
	$gallery = get_post_meta( $post_id, 'gallery', true );
	if ( empty( $gallery ) ) {
		$empty['gallery'] = true;
	}
	foreach ( array( 'android', 'ios', 'link', 'idea', 'idea_copy2', 'التحدي', 'الحل_من_قيمة_تك', 'الخدمة', 'العميل' ) as $field ) {
		$val = get_post_meta( $post_id, $field, true );
		if ( '' === trim( wp_strip_all_tags( (string) $val ) ) ) {
			$empty[ $field ] = true;
		}
	}
	$terms = wp_get_object_terms( $post_id, 'portfolio-categories', array( 'fields' => 'ids' ) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		$empty['category'] = true;
	}

	return $empty;
}

function qeema_import_portfolio_batch() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}

	$progress = qeema_import_portfolio_get_progress();

	if ( true === $progress['done'] ) {
		wp_send_json_success( $progress );
	}

	if ( null === $progress['category_map'] ) {
		$progress['category_map'] = qeema_import_portfolio_load_category_map();
	}

	// One item per batch, not several — a single gap-fill/create can already
	// need an HTML scrape + a media list call + several image downloads
	// against the live (occasionally slow) production site, and a batch of
	// 5 of those in a row was measured hitting PHP's 120s max_execution_time
	// and dying with an uncaught fatal (Requests\Transport\Curl timeout,
	// logged to debug.log) — that's not a Throwable, so the per-item
	// try/catch below can't protect against it. One item keeps every batch
	// safely inside the time limit; it just means more AJAX round-trips.
	$batch_size = 1;
	$list_url   = QEEMA_IMPORT_OLD_SITE . '/wp-json/wp/v2/portfolio?per_page=' . $batch_size . '&page=' . $progress['current_page'] . '&_embed=1';
	$fetched    = qeema_import_remote_get_json( $list_url );

	if ( ! $fetched ) {
		// A transient fetch failure (timeout, momentary outage) is not the
		// same as having finished every page — `current_page` is left
		// untouched so the very next call just retries this same page,
		// and `done` stays false. Marking this done would silently end the
		// import with most items unprocessed and no way to tell it apart
		// from a real completion.
		$progress['errors'][] = 'Page ' . $progress['current_page'] . ': failed to fetch portfolio list from live site (will retry on next call).';
		qeema_import_portfolio_save_progress( $progress );
		wp_send_json_success( $progress );
	}

	if ( null === $progress['total_pages'] ) {
		$total_pages_header = $fetched['headers']['x-wp-totalpages'];
		$progress['total_pages'] = $total_pages_header ? (int) $total_pages_header : 1;
	}

	$items = is_array( $fetched['body'] ) ? $fetched['body'] : array();

	foreach ( $items as $item ) {
		$slug  = isset( $item['slug'] ) ? $item['slug'] : '';
		$title = isset( $item['title']['rendered'] ) ? wp_strip_all_tags( $item['title']['rendered'] ) : '';
		$link  = isset( $item['link'] ) ? $item['link'] : '';

		if ( '' === $slug || '' === $title ) {
			continue;
		}

		try {
			$existing_id = qeema_import_portfolio_find_existing( $slug );

			if ( $existing_id ) {
				$empty = qeema_import_portfolio_empty_fields( $existing_id );
				if ( empty( $empty ) ) {
					$progress['skipped']++;
					$progress['log'][] = "skip (already complete): {$slug}";
					continue;
				}

				$filled_any = qeema_import_portfolio_fill_gaps( $existing_id, $item, $empty, $progress['category_map'] );
				if ( $filled_any ) {
					$progress['gap_filled']++;
					$progress['log'][] = "gap-filled: {$slug}";
				} else {
					$progress['skipped']++;
					$progress['log'][] = "skip (no source data found for gaps): {$slug}";
				}
				continue;
			}

			$new_id = qeema_import_portfolio_create( $item, $progress['category_map'] );
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

	qeema_import_portfolio_save_progress( $progress );
	wp_send_json_success( $progress );
}
add_action( 'wp_ajax_qeema_import_portfolio_batch', 'qeema_import_portfolio_batch' );

/**
 * Fills only currently-empty fields on an already-existing portfolio post.
 * Returns true if anything was actually written.
 */
function qeema_import_portfolio_fill_gaps( $post_id, $item, $empty, $category_map ) {
	$wrote = false;
	$needs_scrape = isset( $empty['idea'] ) || isset( $empty['idea_copy2'] ) || isset( $empty['التحدي'] ) || isset( $empty['الحل_من_قيمة_تك'] ) || isset( $empty['الخدمة'] ) || isset( $empty['android'] ) || isset( $empty['ios'] ) || isset( $empty['link'] );

	$scraped = array();
	if ( $needs_scrape && ! empty( $item['link'] ) ) {
		$title   = isset( $item['title']['rendered'] ) ? wp_strip_all_tags( $item['title']['rendered'] ) : '';
		$html    = qeema_import_remote_get_html( $item['link'] );
		$scraped = qeema_import_scrape_portfolio_page( $html, $title );
	}

	$field_map = array(
		'idea'             => isset( $scraped['idea'] ) ? $scraped['idea'] : '',
		'idea_copy2'       => isset( $scraped['journey'] ) ? $scraped['journey'] : '',
		'التحدي'           => isset( $scraped['challenge'] ) ? $scraped['challenge'] : '',
		'الحل_من_قيمة_تك'  => isset( $scraped['execution'] ) ? $scraped['execution'] : '',
		'الخدمة'           => isset( $scraped['service'] ) ? $scraped['service'] : '',
		'android'          => isset( $scraped['android'] ) ? $scraped['android'] : '',
		'ios'              => isset( $scraped['ios'] ) ? $scraped['ios'] : '',
		'link'             => isset( $scraped['link'] ) ? $scraped['link'] : '',
	);

	foreach ( $field_map as $field => $value ) {
		if ( isset( $empty[ $field ] ) && '' !== $value ) {
			update_post_meta( $post_id, $field, $value );
			$wrote = true;
		}
	}

	if ( isset( $empty['العميل'] ) ) {
		$title = isset( $item['title']['rendered'] ) ? wp_strip_all_tags( $item['title']['rendered'] ) : '';
		if ( '' !== $title ) {
			update_post_meta( $post_id, 'العميل', $title );
			$wrote = true;
		}
	}

	if ( isset( $empty['category'] ) ) {
		$term_ids = qeema_import_portfolio_map_terms( $item, $category_map );
		if ( ! empty( $term_ids ) ) {
			wp_set_object_terms( $post_id, $term_ids, 'portfolio-categories' );
			$wrote = true;
		}
	}

	if ( isset( $empty['thumbnail'] ) || isset( $empty['banner'] ) || isset( $empty['gallery'] ) ) {
		$media = qeema_import_portfolio_fetch_media( $item );
		if ( isset( $empty['thumbnail'] ) && $media['hero_id'] ) {
			set_post_thumbnail( $post_id, $media['hero_id'] );
			$wrote = true;
		}
		if ( isset( $empty['banner'] ) && $media['hero_id'] ) {
			update_post_meta( $post_id, 'banner', $media['hero_id'] );
			$wrote = true;
		}
		if ( isset( $empty['gallery'] ) && ! empty( $media['gallery_ids'] ) ) {
			update_post_meta( $post_id, 'gallery', array_map( 'strval', $media['gallery_ids'] ) );
			$wrote = true;
		}
	}

	return $wrote;
}

function qeema_import_portfolio_map_terms( $item, $category_map ) {
	$term_ids = array();
	$old_ids  = isset( $item['portfolio-categories'] ) ? (array) $item['portfolio-categories'] : array();
	foreach ( $old_ids as $old_id ) {
		if ( isset( $category_map[ $old_id ] ) ) {
			$term_ids[] = $category_map[ $old_id ];
		}
	}
	return $term_ids;
}

/**
 * Fetches the hero image + any additional gallery images for a portfolio
 * item and sideloads them, returning the new attachment IDs. Not attached
 * to a post_id yet at this point — callers attach as needed (thumbnail,
 * banner, gallery all point at real sideloaded media, never a fabricated
 * placeholder).
 */
function qeema_import_portfolio_fetch_media( $item ) {
	$hero_id     = 0;
	$gallery_ids = array();

	$hero_url = '';
	if ( ! empty( $item['_embedded']['wp:featuredmedia'][0]['source_url'] ) ) {
		$hero_url = $item['_embedded']['wp:featuredmedia'][0]['source_url'];
	}

	$media_list = qeema_import_remote_get_json( QEEMA_IMPORT_OLD_SITE . '/wp-json/wp/v2/media?parent=' . (int) $item['id'] . '&per_page=20' );
	$urls       = array();
	if ( $media_list && is_array( $media_list['body'] ) ) {
		foreach ( $media_list['body'] as $media_item ) {
			if ( ! empty( $media_item['source_url'] ) ) {
				$urls[] = $media_item['source_url'];
			}
		}
	}

	if ( '' === $hero_url && ! empty( $urls ) ) {
		$hero_url = array_shift( $urls );
	} elseif ( '' !== $hero_url ) {
		// Don't re-download the hero image a second time into the gallery.
		$urls = array_values( array_diff( $urls, array( $hero_url ) ) );
	}

	if ( '' !== $hero_url ) {
		$hero_id = qeema_import_sideload_image( $hero_url );
	}
	foreach ( $urls as $url ) {
		$id = qeema_import_sideload_image( $url );
		if ( $id ) {
			$gallery_ids[] = $id;
		}
	}

	return array(
		'hero_id'     => $hero_id,
		'gallery_ids' => $gallery_ids,
	);
}

/**
 * Creates a brand-new portfolio post from a live-site REST item + its
 * scraped case-study text. Returns the new post ID, or 0 on failure.
 */
function qeema_import_portfolio_create( $item, $category_map ) {
	$slug  = $item['slug'];
	$title = wp_strip_all_tags( $item['title']['rendered'] );
	$link  = isset( $item['link'] ) ? $item['link'] : '';

	$html    = $link ? qeema_import_remote_get_html( $link ) : '';
	$scraped = qeema_import_scrape_portfolio_page( $html, $title );

	// A short native post_content fallback so the page isn't blank if it's
	// ever rendered without a dedicated Elementor single-portfolio template
	// (none exists yet on this site — a separate, already-flagged gap) —
	// built only from the same real scraped text, nothing invented.
	$content_parts = array_filter( array( $scraped['idea'], $scraped['challenge'], $scraped['execution'], $scraped['journey'] ) );
	$post_content  = implode( "\n\n", array_map( function ( $p ) {
		return '<p>' . esc_html( $p ) . '</p>';
	}, $content_parts ) );

	$post_id = wp_insert_post( array(
		'post_type'      => 'portfolio',
		'post_status'    => 'publish',
		'post_title'     => $title,
		'post_name'      => $slug,
		'post_date'      => isset( $item['date'] ) ? $item['date'] : current_time( 'mysql' ),
		'post_date_gmt'  => isset( $item['date_gmt'] ) ? $item['date_gmt'] : current_time( 'mysql', true ),
		'post_content'   => $post_content,
		'post_excerpt'   => $scraped['idea'] ? wp_trim_words( $scraped['idea'], 30 ) : '',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	), true );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 0;
	}

	if ( $scraped['idea'] ) {
		update_post_meta( $post_id, 'idea', $scraped['idea'] );
	}
	if ( $scraped['challenge'] ) {
		update_post_meta( $post_id, 'التحدي', $scraped['challenge'] );
	}
	if ( $scraped['execution'] ) {
		update_post_meta( $post_id, 'الحل_من_قيمة_تك', $scraped['execution'] );
	}
	if ( $scraped['journey'] ) {
		update_post_meta( $post_id, 'idea_copy2', $scraped['journey'] );
	}
	if ( $scraped['service'] ) {
		update_post_meta( $post_id, 'الخدمة', $scraped['service'] );
	}
	if ( $scraped['android'] ) {
		update_post_meta( $post_id, 'android', $scraped['android'] );
	}
	if ( $scraped['ios'] ) {
		update_post_meta( $post_id, 'ios', $scraped['ios'] );
	}
	if ( $scraped['link'] ) {
		update_post_meta( $post_id, 'link', $scraped['link'] );
	}
	update_post_meta( $post_id, 'العميل', $title );

	$term_ids = qeema_import_portfolio_map_terms( $item, $category_map );
	if ( ! empty( $term_ids ) ) {
		wp_set_object_terms( $post_id, $term_ids, 'portfolio-categories' );
	}

	$media = qeema_import_portfolio_fetch_media( $item );
	if ( $media['hero_id'] ) {
		set_post_thumbnail( $post_id, $media['hero_id'] );
		update_post_meta( $post_id, 'banner', $media['hero_id'] );
	}
	if ( ! empty( $media['gallery_ids'] ) ) {
		update_post_meta( $post_id, 'gallery', array_map( 'strval', $media['gallery_ids'] ) );
	}

	return $post_id;
}

function qeema_import_portfolio_status() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	wp_send_json_success( qeema_import_portfolio_get_progress() );
}
add_action( 'wp_ajax_qeema_import_portfolio_status', 'qeema_import_portfolio_status' );

function qeema_import_portfolio_reset() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	delete_option( qeema_import_portfolio_option_key() );
	wp_send_json_success( qeema_import_portfolio_get_progress() );
}
add_action( 'wp_ajax_qeema_import_portfolio_reset', 'qeema_import_portfolio_reset' );
