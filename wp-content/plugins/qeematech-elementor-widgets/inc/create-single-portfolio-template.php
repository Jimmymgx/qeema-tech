<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One-time, idempotent setup: creates the Elementor Theme Builder "Single
 * Portfolio" template (condition: include/singular/portfolio) so real case
 * studies get a real design instead of falling back to hello-elementor's
 * bare template-parts/single.php — the pre-existing gap where all 166 real
 * portfolio posts stored genuine ACF content (idea/challenge/solution/
 * journey) that no template anywhere ever displayed. Mirrors
 * create-single-post-template.php's exact structure/conventions.
 */
function qeema_single_portfolio_template_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_single_portfolio_template_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => qeema_single_portfolio_template_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => $widget_type,
	);
}

function qeema_maybe_create_single_portfolio_template() {
	if ( get_option( 'qeema_single_portfolio_template_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'elementor_library',
		'meta_key'       => '_elementor_conditions',
		'meta_value'     => 'include/singular/portfolio',
		'meta_compare'   => 'LIKE',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_single_portfolio_template_ready', true, true );
		return;
	}

	$contact_url = '/أتصل-بنا/';

	$hero_section = array(
		'id'       => qeema_single_portfolio_template_qid(),
		'elType'   => 'container',
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_single_portfolio_template_widget( 'qeema-portfolio-case-hero', array(
				'quote_link' => array( 'url' => $contact_url ),
			) ),
		),
		'isInner'  => false,
	);

	$story_section = array(
		'id'       => qeema_single_portfolio_template_qid(),
		'elType'   => 'container',
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_single_portfolio_template_widget( 'qeema-portfolio-case-story' ),
		),
		'isInner'  => false,
	);

	// Live's per-post-cloned case-study layout always includes a client
	// testimonials carousel and a related-articles carousel below the main
	// content - reusing the same sitewide widgets already used elsewhere
	// (blog single template / homepage) rather than building new ones, so
	// this template has the same content sections live does.
	$testimonials_section = array(
		'id'       => qeema_single_portfolio_template_qid(),
		'elType'   => 'container',
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_single_portfolio_template_widget( 'qeema-testimonials-carousel' ),
		),
		'isInner'  => false,
	);

	$related_section = array(
		'id'       => qeema_single_portfolio_template_qid(),
		'elType'   => 'container',
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_single_portfolio_template_widget( 'qeema-related-posts' ),
		),
		'isInner'  => false,
	);

	$cta_section = array(
		'id'       => qeema_single_portfolio_template_qid(),
		'elType'   => 'container',
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_single_portfolio_template_widget( 'qeema-cta-banner', array(
				'heading'    => 'جاهز تبدأ مشروعك القادم؟',
				'subheading' => 'تواصل معنا واحصل على عرض سعر مخصص لمشروعك',
				'buttons'    => array(
					array( 'text' => 'طلب عرض سعر', 'link' => array( 'url' => $contact_url ), 'style' => 'primary' ),
					array( 'text' => 'شاهد أعمالنا', 'link' => array( 'url' => '/أعمالنا/' ), 'style' => 'ghost' ),
				),
			) ),
		),
		'isInner'  => false,
	);

	$layout = array( $hero_section, $story_section, $testimonials_section, $related_section, $cta_section );

	$post_id = wp_insert_post( array(
		'post_title'     => 'Single Portfolio',
		'post_type'      => 'elementor_library',
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return;
	}

	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	// 'single' (not 'single-portfolio') - Elementor Pro only registers dedicated
	// document-type classes for the built-in post/page types; every other post
	// type (including custom ones like `portfolio`) uses the generic 'single'
	// document type. Using a made-up type string here silently breaks
	// Module::get_document() -> Conditions_Cache::regenerate() skips the
	// template with no error, so it never appears in the cached conditions
	// option Elementor actually reads at request time - confirmed by testing.
	update_post_meta( $post_id, '_elementor_template_type', 'single' );
	update_post_meta( $post_id, '_elementor_version', '3.35.8' );
	update_post_meta( $post_id, '_elementor_conditions', array( 'include/singular/portfolio' ) );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );

	if ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Cache' ) ) {
		( new \ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Cache() )->regenerate();
	}
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'qeema_single_portfolio_template_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_single_portfolio_template', 20 );
