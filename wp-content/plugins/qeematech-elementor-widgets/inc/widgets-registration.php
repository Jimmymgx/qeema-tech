<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget category grouping: one category per page the widget was pulled
 * from, plus a shared category for anything reused across more than one
 * page (per the "one widget per unique design, reused" decision — a widget
 * used on both the homepage and the ODOO page can't honestly live under
 * just one page's category).
 */
function qeema_register_widget_categories( $elements_manager ) {
	$elements_manager->add_category( 'qeema-shared-sections', array(
		'title' => __( 'Qeematech - Shared Sections', 'qeematech-elementor-widgets' ),
		'icon'  => 'fa fa-plug',
	) );
}
add_action( 'elementor/elements/categories_registered', 'qeema_register_widget_categories' );

/**
 * Only scripts are registered here — all CSS for these widgets now lives in
 * the qeematech-custom-css plugin's single consolidated stylesheet, enqueued
 * at priority 999 so it reliably wins the cascade against Elementor's own
 * global/kit CSS.
 */
function qeema_register_widget_assets() {
	$plugin_url = plugin_dir_url( __DIR__ );
	$version    = '0.1.0';

	wp_register_script( 'qeema-hero-section', $plugin_url . 'assets/js/hero-section.js', array( 'jquery' ), $version, true );
	wp_register_script( 'qeema-stats-counter', $plugin_url . 'assets/js/stats-counter.js', array( 'jquery' ), $version, true );
	wp_register_script( 'qeema-site-header', $plugin_url . 'assets/js/site-header.js', array( 'jquery' ), $version, true );
	wp_register_script( 'qeema-testimonials-carousel', $plugin_url . 'assets/js/testimonials-carousel.js', array( 'jquery' ), $version, true );

	wp_enqueue_style( 'font-awesome-5-all' );
}
add_action( 'wp_enqueue_scripts', 'qeema_register_widget_assets' );
add_action( 'elementor/editor/before_enqueue_scripts', 'qeema_register_widget_assets' );

function qeema_register_widgets( $widgets_manager ) {
	require_once __DIR__ . '/../widgets/hero-section-widget.php';
	require_once __DIR__ . '/../widgets/stats-counter-widget.php';
	require_once __DIR__ . '/../widgets/feature-grid-widget.php';
	require_once __DIR__ . '/../widgets/site-header-widget.php';
	require_once __DIR__ . '/../widgets/site-footer-widget.php';
	require_once __DIR__ . '/../widgets/portfolio-teaser-widget.php';
	require_once __DIR__ . '/../widgets/testimonials-carousel-widget.php';

	$widgets_manager->register( new \Qeema_Hero_Section_Widget() );
	$widgets_manager->register( new \Qeema_Stats_Counter_Widget() );
	$widgets_manager->register( new \Qeema_Feature_Grid_Widget() );
	$widgets_manager->register( new \Qeema_Site_Header_Widget() );
	$widgets_manager->register( new \Qeema_Site_Footer_Widget() );
	$widgets_manager->register( new \Qeema_Portfolio_Teaser_Widget() );
	$widgets_manager->register( new \Qeema_Testimonials_Carousel_Widget() );
}
add_action( 'elementor/widgets/register', 'qeema_register_widgets' );
