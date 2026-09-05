<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Populates the (currently empty) `ourclient` ACF options-page logo
 * repeater from the live site's `/عملائنا/` page. The old site shows two
 * overlapping logo galleries (44 logos from 2024, 48 from 2025); per the
 * user's choice, only the newer/larger 2025 set is imported. No names or
 * other metadata exist for these logos on the old site either — this is a
 * pure image import.
 *
 * Unlike the other two importers, there's nothing to reconcile against
 * (the repeater starts empty), so this doesn't need per-item existence
 * checks — it batches a few logos per call (lighter than an image+scrape
 * item, closer in cost to the blog importer's plain image sideloads) and
 * accumulates the full repeater array across calls, writing it to the
 * options page only once every logo has been sideloaded.
 */

function qeema_import_clients_option_key() {
	return 'qeema_clients_import_progress';
}

function qeema_import_clients_get_progress() {
	$default = array(
		'urls'          => null, // scraped list of logo image URLs
		'attachment_ids'=> array(),
		'current_page'  => 1,
		'total_pages'   => null,
		'created'       => 0,
		'skipped'       => 0,
		'errors'        => array(),
		'log'           => array(),
		'done'          => false,
		'saved_to_options' => false,
	);
	$stored = get_option( qeema_import_clients_option_key(), array() );
	return wp_parse_args( $stored, $default );
}

function qeema_import_clients_save_progress( $progress ) {
	if ( count( $progress['log'] ) > 200 ) {
		$progress['log'] = array_slice( $progress['log'], -200 );
	}
	if ( count( $progress['errors'] ) > 200 ) {
		$progress['errors'] = array_slice( $progress['errors'], -200 );
	}
	update_option( qeema_import_clients_option_key(), $progress, false );
}

/**
 * Extracts the 2025/05-uploaded logo image URLs from the clients page — a
 * plain regex over the raw HTML is enough here (confirmed by direct
 * inspection: these are simple <img>/data-src references, no per-item
 * markup worth a heavier DOM-block parse like the other two importers
 * need).
 */
function qeema_import_scrape_client_logos( $html ) {
	if ( empty( $html ) ) {
		return array();
	}
	preg_match_all( '#https://www\.qeematech\.net/wp-content/uploads/2025/05/[^"\'\s)]+\.(?:webp|png|jpe?g)#i', $html, $matches );
	return array_values( array_unique( $matches[0] ) );
}

function qeema_import_clients_batch() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}

	$progress = qeema_import_clients_get_progress();

	if ( true === $progress['done'] ) {
		if ( ! $progress['saved_to_options'] && ! empty( $progress['attachment_ids'] ) && function_exists( 'update_field' ) ) {
			$rows = array_map( function ( $id ) {
				return array( 'logo' => $id );
			}, $progress['attachment_ids'] );
			update_field( 'client', $rows, 'option' );
			$progress['saved_to_options'] = true;
			qeema_import_clients_save_progress( $progress );
		}
		wp_send_json_success( $progress );
	}

	if ( null === $progress['urls'] ) {
		$html = qeema_import_remote_get_html( QEEMA_IMPORT_OLD_SITE . '/%D8%B9%D9%85%D9%84%D8%A7%D8%A6%D9%86%D8%A7/' );
		if ( empty( $html ) ) {
			$progress['errors'][] = 'Failed to fetch clients page from live site (will retry on next call).';
			qeema_import_clients_save_progress( $progress );
			wp_send_json_success( $progress );
		}
		$progress['urls'] = qeema_import_scrape_client_logos( $html );
	}

	$urls  = $progress['urls'];
	$total = count( $urls );
	if ( null === $progress['total_pages'] ) {
		$progress['total_pages'] = $total;
	}

	$batch_size = 3;
	$start      = $progress['current_page'] - 1;
	$slice      = array_slice( $urls, $start, $batch_size );

	foreach ( $slice as $url ) {
		try {
			$id = qeema_import_sideload_image( $url, 0, 'Client logo' );
			if ( $id ) {
				$progress['attachment_ids'][] = $id;
				$progress['created']++;
				$progress['log'][] = 'sideloaded: ' . wp_basename( $url );
			} else {
				$progress['errors'][] = 'failed to sideload: ' . wp_basename( $url );
			}
		} catch ( \Throwable $e ) {
			$progress['errors'][] = 'exception on ' . wp_basename( $url ) . ': ' . $e->getMessage();
		}
	}

	$progress['current_page'] += count( $slice );
	if ( $progress['current_page'] > $total ) {
		$progress['done'] = true;
	}

	qeema_import_clients_save_progress( $progress );
	wp_send_json_success( $progress );
}
add_action( 'wp_ajax_qeema_import_clients_batch', 'qeema_import_clients_batch' );

function qeema_import_clients_status() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	wp_send_json_success( qeema_import_clients_get_progress() );
}
add_action( 'wp_ajax_qeema_import_clients_status', 'qeema_import_clients_status' );

function qeema_import_clients_reset() {
	check_ajax_referer( 'qeema_live_import', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	delete_option( qeema_import_clients_option_key() );
	wp_send_json_success( qeema_import_clients_get_progress() );
}
add_action( 'wp_ajax_qeema_import_clients_reset', 'qeema_import_clients_reset' );
