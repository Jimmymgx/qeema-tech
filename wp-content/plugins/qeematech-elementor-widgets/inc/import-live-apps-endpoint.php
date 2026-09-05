<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Batch importer: pulls all 56 real apps from the live site's `/live-app/`
 * showcase archive into this site's (currently empty) `live-apps` CPT, then
 * rewires the Live App page to actually read from it (see
 * qeema_refresh_live_app_page_from_cpt() in create-live-app-page.php) —
 * replacing the smaller ~10-app set that page previously sourced from the
 * `portfolio` CPT as a stand-in while `live-apps` had no data.
 *
 * Like the portfolio importer, the old site's ACF fields for this CPT are
 * NOT REST-exposed (confirmed: `acf` is always `[]`), so description +
 * store-link data only exists in the rendered archive HTML. Unlike
 * portfolio (159 items across many sitemap pages), all 56 live-apps live on
 * ONE archive page — fetched and parsed once, cached in the progress
 * option, then processed one item per batch (same reasoning as the
 * portfolio importer: an item's own image sideload can be slow enough that
 * batching several together risks tripping PHP's max_execution_time).
 */

function qeema_import_live_apps_option_key() {
	return 'qeema_live_apps_import_progress';
}

function qeema_import_live_apps_get_progress() {
	$default = array(
		'items'          => null, // cached REST list (id/slug/title/icon_url)
		'scraped'        => null, // cached parsed archive data: id => {description, google_play_link, apple_link}
		// Named current_page/total_pages (rather than a plain item cursor)
		// to match the shape the shared admin-page JS (live-import-admin-page.php)
		// already expects from the portfolio/blog importers — here "page" is
		// just one item, same as portfolio's batch-size-1 pages.
		'current_page'   => 1,
		'total_pages'    => null,
		'created'        => 0,
		'skipped'        => 0,
		'errors'         => array(),
		'log'            => array(),
		'done'           => false,
		'page_refreshed' => false,
	);
	$stored = get_option( qeema_import_live_apps_option_key(), array() );
	return wp_parse_args( $stored, $default );
}

function qeema_import_live_apps_save_progress( $progress ) {
	if ( count( $progress['log'] ) > 200 ) {
		$progress['log'] = array_slice( $progress['log'], -200 );
	}
	if ( count( $progress['errors'] ) > 200 ) {
		$progress['errors'] = array_slice( $progress['errors'], -200 );
	}
	update_option( qeema_import_live_apps_option_key(), $progress, false );
}

/**
 * Parses the live-app archive page into a map keyed by the old site's
 * numeric post ID (matching the REST `id` field), each holding whatever of
 * description/google_play_link/apple_link this heuristic scrape actually
 * found — never fabricated for items where a piece is genuinely absent.
 * Blocks are delimited by Elementor's own loop-item marker
 * (`data-elementor-type="loop-item" ... e-loop-item-{id}`), confirmed by
 * direct inspection to appear exactly once per app, in document order.
 */
function qeema_import_scrape_live_apps_archive( $html ) {
	$map = array();
	if ( empty( $html ) ) {
		return $map;
	}

	preg_match_all( '/data-elementor-type="loop-item" data-elementor-id="\d+" class="elementor elementor-\d+ e-loop-item e-loop-item-(\d+)/', $html, $matches, PREG_OFFSET_CAPTURE );

	$count = count( $matches[1] );
	for ( $i = 0; $i < $count; $i++ ) {
		$id    = (int) $matches[1][ $i ][0];
		$start = $matches[1][ $i ][1];
		$end   = ( $i + 1 < $count ) ? $matches[1][ $i + 1 ][1] : strlen( $html );
		$chunk = substr( $html, $start, $end - $start );

		$entry = array(
			'description'      => '',
			'google_play_link' => '',
			'apple_link'       => '',
		);

		if ( preg_match( '/href="(https?:\/\/play\.google\.com\/[^"]+)"/i', $chunk, $m ) ) {
			$entry['google_play_link'] = html_entity_decode( $m[1], ENT_QUOTES );
		}
		if ( preg_match( '/href="(https?:\/\/apps\.apple\.com\/[^"]+)"/i', $chunk, $m ) ) {
			$entry['apple_link'] = html_entity_decode( $m[1], ENT_QUOTES );
		}
		if ( preg_match( '/elementor-widget-text-editor"[^>]*>\s*<div class="elementor-widget-container">\s*([^<]+)</u', $chunk, $m ) ) {
			$entry['description'] = qeema_import_mb_trim( html_entity_decode( trim( $m[1] ), ENT_QUOTES ) );
		}

		$map[ $id ] = $entry;
	}

	return $map;
}

function qeema_import_live_apps_batch() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}

	$progress = qeema_import_live_apps_get_progress();

	if ( true === $progress['done'] ) {
		if ( ! $progress['page_refreshed'] ) {
			qeema_refresh_live_app_page_from_cpt();
			$progress['page_refreshed'] = true;
			qeema_import_live_apps_save_progress( $progress );
		}
		wp_send_json_success( $progress );
	}

	if ( null === $progress['items'] ) {
		$fetched = qeema_import_remote_get_json( QEEMA_IMPORT_OLD_SITE . '/wp-json/wp/v2/live-apps?per_page=100&_embed=1' );
		if ( ! $fetched || ! is_array( $fetched['body'] ) ) {
			$progress['errors'][] = 'Failed to fetch live-apps list from live site (will retry on next call).';
			qeema_import_live_apps_save_progress( $progress );
			wp_send_json_success( $progress );
		}
		$items = array();
		foreach ( $fetched['body'] as $item ) {
			$icon_url = '';
			if ( ! empty( $item['_embedded']['wp:featuredmedia'][0]['source_url'] ) ) {
				$icon_url = $item['_embedded']['wp:featuredmedia'][0]['source_url'];
			}
			$items[] = array(
				'id'    => (int) $item['id'],
				'slug'  => isset( $item['slug'] ) ? $item['slug'] : '',
				'title' => isset( $item['title']['rendered'] ) ? wp_strip_all_tags( $item['title']['rendered'] ) : '',
				'icon'  => $icon_url,
			);
		}
		$progress['items'] = $items;
	}

	if ( null === $progress['scraped'] ) {
		$html = qeema_import_remote_get_html( QEEMA_IMPORT_OLD_SITE . '/live-app/' );
		if ( empty( $html ) ) {
			$progress['errors'][] = 'Failed to fetch live-app archive page (will retry on next call).';
			qeema_import_live_apps_save_progress( $progress );
			wp_send_json_success( $progress );
		}
		$progress['scraped'] = qeema_import_scrape_live_apps_archive( $html );
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
			$existing = get_posts( array(
				'post_type'      => 'live-apps',
				'name'           => $item['slug'],
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			) );

			if ( ! empty( $existing ) ) {
				$progress['skipped']++;
				$progress['log'][] = "skip (already exists): {$item['slug']}";
			} else {
				$scraped = isset( $progress['scraped'][ $item['id'] ] ) ? $progress['scraped'][ $item['id'] ] : array(
					'description'      => '',
					'google_play_link' => '',
					'apple_link'       => '',
				);

				$new_id = wp_insert_post( array(
					'post_type'      => 'live-apps',
					'post_status'    => 'publish',
					'post_title'     => $item['title'],
					'post_name'      => $item['slug'],
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				), true );

				if ( is_wp_error( $new_id ) || ! $new_id ) {
					$progress['errors'][] = "failed to create: {$item['slug']}";
				} else {
					if ( $scraped['description'] ) {
						update_post_meta( $new_id, 'description', $scraped['description'] );
					}
					if ( $scraped['google_play_link'] ) {
						update_post_meta( $new_id, 'google_play_link', $scraped['google_play_link'] );
					}
					if ( $scraped['apple_link'] ) {
						update_post_meta( $new_id, 'apple_link', $scraped['apple_link'] );
					}
					if ( $item['icon'] ) {
						$attachment_id = qeema_import_sideload_image( $item['icon'], $new_id, $item['title'] );
						if ( $attachment_id ) {
							set_post_thumbnail( $new_id, $attachment_id );
						}
					}
					$progress['created']++;
					$progress['log'][] = "created: {$item['slug']}";
				}
			}
		} catch ( \Throwable $e ) {
			$progress['errors'][] = "exception on {$item['slug']}: " . $e->getMessage();
		}

		$progress['current_page']++;
	}

	if ( $progress['current_page'] > $total ) {
		$progress['done'] = true;
	}

	qeema_import_live_apps_save_progress( $progress );
	wp_send_json_success( $progress );
}
add_action( 'wp_ajax_qeema_import_live_apps_batch', 'qeema_import_live_apps_batch' );

function qeema_import_live_apps_status() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	wp_send_json_success( qeema_import_live_apps_get_progress() );
}
add_action( 'wp_ajax_qeema_import_live_apps_status', 'qeema_import_live_apps_status' );

function qeema_import_live_apps_reset() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	delete_option( qeema_import_live_apps_option_key() );
	wp_send_json_success( qeema_import_live_apps_get_progress() );
}
add_action( 'wp_ajax_qeema_import_live_apps_reset', 'qeema_import_live_apps_reset' );
