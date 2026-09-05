<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the آراء العملاء (Testimonials) page — the nav already has this
 * link (same "nav item exists, page doesn't" pattern as every other page
 * built this session). Reuses testimonials-carousel-widget.php AS-IS with
 * items_count:-1: it already queries the real `testimonial` CPT and already
 * renders exactly the old site's actual pattern (client image + video
 * popup) — this page was initially misread as a written-quote archive; it's
 * actually a video-testimonial gallery, which is precisely what this widget
 * already does. No new widget needed.
 */
function qeema_testimonials_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_testimonials_page_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => qeema_testimonials_page_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => $widget_type,
	);
}

function qeema_maybe_create_testimonials_page() {
	if ( get_option( 'qeema_testimonials_page_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'page',
		'name'           => 'آراء-العملاء',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_testimonials_page_ready', true, true );
		return;
	}

	$heading_section = array(
		'id'       => qeema_testimonials_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_testimonials_page_widget( 'qeema-about-hero', array(
				'eyebrow'        => '',
				'heading'        => 'آراء العملاء',
				'heading_tag'    => 'h1',
				'subheading'     => 'شاهد آراء العملاء السابقين للشركة قيمة تك وشاهد أيضا اخر اعمال الشركة، أقوال العملاء السابقين قد تلقي الضوء على الأشياء التي تساعدك في تسهيل اتخاذ قرارك.',
				'visual_variant' => 'none',
				'buttons'        => array(),
			) ),
		),
	);

	$testimonials_section = array(
		'id'       => qeema_testimonials_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_testimonials_page_widget( 'qeema-testimonials-carousel', array(
				'heading'     => '',
				'subheading'  => '',
				'items_count' => -1,
			) ),
		),
	);

	$layout = array( $heading_section, $testimonials_section );

	$page_id = wp_insert_post( array(
		'post_title'     => 'آراء العملاء',
		'post_name'      => 'آراء-العملاء',
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

	$stored         = get_post_meta( $page_id, '_elementor_data', true );
	$decoded        = json_decode( $stored, true );
	$decode_ok      = is_array( $decoded ) && 2 === count( $decoded );
	$has_carousel   = false !== strpos( (string) $stored, 'qeema-testimonials-carousel' );

	if ( ! $decode_ok || ! $has_carousel ) {
		wp_delete_post( $page_id, true );
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	flush_rewrite_rules( false );

	update_post_meta( $page_id, 'rank_math_description', 'تعرف على آراء عملاء قيمة تك السابقين وتجاربهم الحقيقية معنا في تصميم المواقع وتطوير التطبيقات — شهادات فيديو حقيقية من عملاء استفادوا من خدماتنا.' );

	update_option( 'qeema_testimonials_page_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_testimonials_page', 20 );
