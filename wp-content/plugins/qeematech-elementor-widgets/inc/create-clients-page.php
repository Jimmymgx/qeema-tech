<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the عملائنا (Our Clients) page. Old site's real page turned out to
 * be just two client-logo displays (a static gallery grid + a scrolling
 * strip of the same logos) — no written/video testimonials despite raw-HTML
 * false positives from unused global CSS/JS class names on that page.
 * trusted-by-logos-widget.php already reads the real `ourclient` ACF
 * options-page repeater and has exactly one display mode (auto-switching
 * marquee/static-row by logo count) — reused once here rather than forcing
 * a second, redundant marquee of the same logos.
 */
function qeema_clients_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_clients_page_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => qeema_clients_page_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => $widget_type,
	);
}

function qeema_maybe_create_clients_page() {
	if ( get_option( 'qeema_clients_page_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'page',
		'name'           => 'عملائنا',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_clients_page_ready', true, true );
		return;
	}

	$heading_section = array(
		'id'       => qeema_clients_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_clients_page_widget( 'qeema-about-hero', array(
				'eyebrow'        => '',
				'heading'        => 'عملائنا',
				'heading_tag'    => 'h1',
				'subheading'     => 'مجموعة من عملاء قيمة تك المميزين حيث قمنا ببناء علاقة قوية تقوم على أسس المنفعة المتبادلة، لذا ساهم تطوير مشاريع عملائنا على تحقيق التنمية بشكل إيجابي في أعمالهم لتتحول العلاقة الى شراكة نجاح.',
				'visual_variant' => 'none',
				'buttons'        => array(),
			) ),
		),
	);

	$logos_section = array(
		'id'       => qeema_clients_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_clients_page_widget( 'qeema-trusted-by', array(
				'eyebrow' => '',
				'heading' => '',
				'speed'   => 32,
			) ),
		),
	);

	$layout = array( $heading_section, $logos_section );

	$page_id = wp_insert_post( array(
		'post_title'     => 'عملائنا',
		'post_name'      => 'عملائنا',
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
	$decode_ok = is_array( $decoded ) && 2 === count( $decoded );
	$has_logos = false !== strpos( (string) $stored, 'qeema-trusted-by' );

	if ( ! $decode_ok || ! $has_logos ) {
		wp_delete_post( $page_id, true );
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	flush_rewrite_rules( false );

	update_post_meta( $page_id, 'rank_math_description', 'تعرف على عملاء قيمة تك المميزين من مختلف القطاعات، وشراكات النجاح التي بنيناها معًا في تصميم المواقع وتطوير التطبيقات والمتاجر الإلكترونية.' );

	update_option( 'qeema_clients_page_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_clients_page', 20 );
