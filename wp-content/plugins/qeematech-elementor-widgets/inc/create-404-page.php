<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the site's Elementor Pro Theme Builder "Error 404" template —
 * registered the same way Elementor Pro's own Theme Builder would (an
 * elementor_library post with _elementor_template_type 'error-404' and an
 * `include/singular/not_found404` condition, matching
 * elementor-pro/modules/theme-builder/documents/error-404.php and
 * conditions/not-found404.php exactly) so it's picked up automatically on
 * any is_404() request, with no manual wp-admin step needed. Built from
 * plain native Elementor widgets (heading/text-editor/button), same as
 * create-thank-you-page.php — this is static content, no custom widget
 * needed. Visual treatment (the large gradient "404" numeral, spacing) is
 * styled via qeema-custom-css under the .qeema-404 classes, not inline
 * widget settings, matching this project's styling convention.
 */
function qeema_404_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_404_page_heading( $title, $tag, $css_classes = '' ) {
	$settings = array( 'title' => $title, 'header_size' => $tag, 'align' => 'center' );
	if ( $css_classes ) {
		$settings['_css_classes'] = $css_classes;
	}
	return array(
		'id'         => qeema_404_page_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => 'heading',
	);
}

function qeema_404_page_text( $html ) {
	return array(
		'id'         => qeema_404_page_qid(),
		'elType'     => 'widget',
		'settings'   => array( 'editor' => $html, 'align' => 'center', '_css_classes' => 'qeema-404__lead' ),
		'elements'   => array(),
		'widgetType' => 'text-editor',
	);
}

function qeema_404_page_button( $text, $url, $style = 'qeema-404__btn-primary' ) {
	return array(
		'id'         => qeema_404_page_qid(),
		'elType'     => 'widget',
		'settings'   => array( 'text' => $text, 'link' => array( 'url' => $url ), 'align' => 'center', '_css_classes' => $style ),
		'elements'   => array(),
		'widgetType' => 'button',
	);
}

function qeema_maybe_create_404_page() {
	if ( get_option( 'qeema_404_page_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'elementor_library',
		'meta_key'       => '_elementor_template_type',
		'meta_value'     => 'error-404',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		// The document already exists (e.g. a partial earlier run created it
		// before this cache-regenerate step existed) - still make sure
		// Theme Builder's routing cache actually knows about it, same as the
		// fresh-create path below, before marking this done for good.
		if ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
			\ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_cache()->regenerate();
		}
		update_option( 'qeema_404_page_ready', true, true );
		return;
	}

	$elements = array(
		qeema_404_page_heading( '404', 'h1', 'qeema-404__numeral' ),
		qeema_404_page_heading( 'عذرًا، الصفحة غير موجودة', 'h2', 'qeema-404__subheading' ),
		qeema_404_page_text( '<p>يبدو أن الرابط الذي تحاول الوصول إليه غير صحيح أو تم نقل الصفحة. يمكنك العودة للرئيسية أو تصفح خدماتنا وأعمالنا من الروابط التالية.</p>' ),
		array(
			'id'       => qeema_404_page_qid(),
			'elType'   => 'container',
			'isInner'  => true,
			'settings' => array( 'content_width' => 'full', 'css_classes' => 'qeema-404__actions' ),
			'elements' => array(
				qeema_404_page_button( 'العودة للرئيسية', '/' ),
				qeema_404_page_button( 'تصفح خدماتنا', '/خدماتنا/', 'qeema-404__btn-ghost' ),
				qeema_404_page_button( 'تواصل معنا', '/أتصل-بنا/', 'qeema-404__btn-ghost' ),
			),
		),
	);

	$section = array(
		'id'       => qeema_404_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full', 'css_classes' => 'qeema-404' ),
		'elements' => $elements,
	);

	$layout = array( $section );

	$page_id = wp_insert_post( array(
		'post_title'     => 'Error 404',
		'post_type'      => 'elementor_library',
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) );

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'error-404' );
	update_post_meta( $page_id, '_elementor_version', '3.35.8' );
	update_post_meta( $page_id, '_elementor_pro_version', '3.34.1' );
	update_post_meta( $page_id, '_wp_page_template', 'default' );
	// Matches ElementorPro\Modules\ThemeBuilder\Conditions\Not_Found404::get_type()/get_name()
	// ('singular' / 'not_found404'), parsed by Conditions_Manager::parse_condition()
	// as {type}/{name}/{sub_name} - same shape as this project's existing
	// include/singular/post and include/singular/portfolio templates.
	update_post_meta( $page_id, '_elementor_conditions', array( 'include/singular/not_found404' ) );

	$json = wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );

	$stored     = get_post_meta( $page_id, '_elementor_data', true );
	$decoded    = json_decode( $stored, true );
	$decode_ok  = is_array( $decoded ) && 1 === count( $decoded );
	$has_markup = false !== strpos( (string) $stored, 'الصفحة غير موجودة' );

	if ( ! $decode_ok || ! $has_markup ) {
		wp_delete_post( $page_id, true );
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	// Theme Builder's location routing (locations-manager.php) matches
	// documents against a persistent conditions CACHE, not a live postmeta
	// query - it's normally rebuilt by Conditions_Manager::save_conditions()
	// when a template's conditions are set through Elementor's own editor.
	// Writing _elementor_conditions directly via update_post_meta() bypasses
	// that, so without this the new document is invisible to routing and
	// the theme's default 404 template keeps rendering instead.
	if ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
		\ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_cache()->regenerate();
	}

	update_option( 'qeema_404_page_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_404_page', 20 );
