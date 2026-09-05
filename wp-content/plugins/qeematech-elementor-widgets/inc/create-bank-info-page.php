<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the حسابات شركة قيمة تك البنكية (bank transfer info) page — the
 * old site's own version is just as plain: a heading + 7 label/value text
 * rows, no table, no images. Matched here with native Elementor `heading`
 * widgets (settings key `title`) rather than any custom widget, since the
 * old site's own structure doesn't need one.
 */
function qeema_bank_info_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_bank_info_page_heading( $title, $tag = 'h2' ) {
	return array(
		'id'         => qeema_bank_info_page_qid(),
		'elType'     => 'widget',
		'settings'   => array(
			'title'       => $title,
			'header_size' => $tag,
			'align'       => 'center',
		),
		'elements'   => array(),
		'widgetType' => 'heading',
	);
}

function qeema_maybe_create_bank_info_page() {
	if ( get_option( 'qeema_bank_info_page_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'page',
		'name'           => 'حسابات-شركة-قيمة-تك-البنكية',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_bank_info_page_ready', true, true );
		return;
	}

	// Verbatim from the old site's actual page.
	$rows = array(
		'Bank Name: BANQE MISR',
		'Account Name: Qeama Tech قيمة تك',
		'Account Num: 5500001000005804',
		'IBAN: EG870002055005500001000005804',
		'Branch: 550-Makram Ebeid',
		'Swift Code: BMISEGTXXXX',
		'Currency: EGP',
	);

	$headings = array( qeema_bank_info_page_heading( 'بيانات الحساب البنكي', 'h1' ) );
	foreach ( $rows as $row ) {
		$headings[] = qeema_bank_info_page_heading( $row, 'h2' );
	}

	$section = array(
		'id'       => qeema_bank_info_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => $headings,
	);

	$layout = array( $section );

	$page_id = wp_insert_post( array(
		'post_title'     => 'حسابات شركة قيمة تك البنكية',
		'post_name'      => 'حسابات-شركة-قيمة-تك-البنكية',
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) );

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', '3.35.8' );
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $page_id, '_ez-toc-disabled', 1 );

	$json = wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );

	$stored    = get_post_meta( $page_id, '_elementor_data', true );
	$decoded   = json_decode( $stored, true );
	$decode_ok = is_array( $decoded ) && 1 === count( $decoded );
	$has_iban  = false !== strpos( (string) $stored, 'IBAN' );

	if ( ! $decode_ok || ! $has_iban ) {
		wp_delete_post( $page_id, true );
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	flush_rewrite_rules( false );

	update_post_meta( $page_id, 'rank_math_description', 'بيانات الحساب البنكي لشركة قيمة تك للتحويلات المصرفية — اسم البنك، رقم الحساب، الآيبان، وكود السويفت.' );

	update_option( 'qeema_bank_info_page_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_bank_info_page', 20 );
