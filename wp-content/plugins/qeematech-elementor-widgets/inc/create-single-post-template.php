<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One-time, idempotent setup: creates the Elementor Theme Builder "Single
 * Post" template (condition: include/singular/post) so real blog posts get
 * a real design instead of falling back to hello-elementor's bare
 * template-parts/single.php. All layout composition (main column + fixed
 * sidebar, responsive collapse) is done via CSS classes on plain containers
 * rather than hand-authored Elementor flex-item control keys, matching how
 * every other page on this site keeps its actual layout logic in the
 * consolidated stylesheet.
 *
 * Also flips Rank Math's breadcrumbs option on — required for the
 * [rank_math_breadcrumb] shortcode used in this template to output
 * anything; Elementor Pro's own native Breadcrumbs widget hard-depends on
 * Yoast SEO (confirmed via its source), which isn't installed here.
 */
function qeema_single_post_template_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_single_post_template_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => qeema_single_post_template_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => $widget_type,
	);
}

/**
 * Elementor Pro's Post Title / Featured Image widgets only show the
 * current post's real title/image when their 'title'/'image' control is
 * explicitly bound to the matching dynamic tag — the "dynamic default"
 * shown in a fresh editor session is a JS-only convenience, not something
 * PHP resolves on its own for hand-authored _elementor_data. Confirmed by
 * testing: leaving the control unset renders the widget's own static
 * placeholder ("Add Your Heading Text Here" / a gray placeholder image)
 * instead of the post's real content.
 */
function qeema_single_post_template_dynamic_tag( $tag_name ) {
	$id = qeema_single_post_template_qid();
	return '[elementor-tag id="' . $id . '" name="' . $tag_name . '" settings="%7B%7D"]';
}

function qeema_maybe_create_single_post_template() {
	// Cached flag so steady-state requests (this hook runs on every front-end
	// AND wp-admin request via 'init') skip the query below entirely once the
	// template is confirmed to exist — options are already loaded into memory
	// per-request via alloptions, so this check costs nothing extra.
	if ( get_option( 'qeema_single_post_template_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'elementor_library',
		'meta_key'       => '_elementor_template_type',
		'meta_value'     => 'single-post',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_single_post_template_ready', true, true );
		return;
	}

	$contact_url = '/أتصل-بنا/';

	$main_column = array(
		'id'       => qeema_single_post_template_qid(),
		'elType'   => 'container',
		'isInner'  => true,
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'qeema-post-main',
		),
		'elements' => array(
			qeema_single_post_template_widget( 'theme-post-featured-image', array(
				'__dynamic__' => array( 'image' => qeema_single_post_template_dynamic_tag( 'post-featured-image' ) ),
			) ),
			qeema_single_post_template_widget( 'theme-post-content' ),
			qeema_single_post_template_widget( 'qeema-share-buttons' ),
			qeema_single_post_template_widget( 'button', array(
				'text' => 'طلب عرض سعر',
				'link' => array( 'url' => $contact_url ),
			) ),
		),
	);

	$sidebar_column = array(
		'id'       => qeema_single_post_template_qid(),
		'elType'   => 'container',
		'isInner'  => true,
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'qeema-post-sidebar',
		),
		'elements' => array(
			qeema_single_post_template_widget( 'qeema-sidebar-cta', array(
				'button_link' => array( 'url' => $contact_url ),
			) ),
			qeema_single_post_template_widget( 'qeema-post-categories' ),
		),
	);

	$hero_section = array(
		'id'       => qeema_single_post_template_qid(),
		'elType'   => 'container',
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'qeema-post-hero',
		),
		'elements' => array(
			qeema_single_post_template_widget( 'shortcode', array( 'shortcode' => '[rank_math_breadcrumb]' ) ),
			qeema_single_post_template_widget( 'theme-post-title', array(
				'__dynamic__' => array( 'title' => qeema_single_post_template_dynamic_tag( 'post-title' ) ),
			) ),
		),
		'isInner'  => false,
	);

	$related_section = array(
		'id'       => qeema_single_post_template_qid(),
		'elType'   => 'container',
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'qeema-post-related-section',
		),
		'elements' => array(
			qeema_single_post_template_widget( 'qeema-related-posts' ),
		),
		'isInner'  => false,
	);

	$layout = array(
		$hero_section,
		array(
			'id'       => qeema_single_post_template_qid(),
			'elType'   => 'container',
			'settings' => array(
				'content_width' => 'full',
				'css_classes'   => 'qeema-post-layout',
			),
			'elements' => array( $main_column, $sidebar_column ),
			'isInner'  => false,
		),
		$related_section,
	);

	$post_id = wp_insert_post( array(
		'post_title'     => 'Single Post',
		'post_type'      => 'elementor_library',
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return;
	}

	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'single-post' );
	update_post_meta( $post_id, '_elementor_version', '3.35.8' );
	update_post_meta( $post_id, '_elementor_conditions', array( 'include/singular/post' ) );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );

	if ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Cache' ) ) {
		( new \ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Cache() )->regenerate();
	}
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	// Rank Math's entire frontend module (breadcrumbs, meta title/description
	// output, schema, everything) stays completely dormant until its setup
	// wizard is either connected or explicitly skipped — otherwise
	// Registration::$invalid short-circuits the plugin's own instantiate()
	// before it ever hooks plugins_loaded => init_frontend(). This is the
	// same effect as clicking "Skip" in the wizard.
	if ( ! get_option( 'rank_math_registration_skip' ) ) {
		update_option( 'rank_math_registration_skip', true );
	}

	$rank_math_general = get_option( 'rank-math-options-general', array() );
	if ( ! isset( $rank_math_general['breadcrumbs'] ) || 'off' === $rank_math_general['breadcrumbs'] ) {
		$rank_math_general['breadcrumbs'] = 'on';
		update_option( 'rank-math-options-general', $rank_math_general );
	}

	update_option( 'qeema_single_post_template_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_single_post_template', 20 );
