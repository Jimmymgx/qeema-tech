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

function qeema_register_widget_assets() {
	$plugin_url = plugin_dir_url( __DIR__ );
	$version    = '0.1.0';

	wp_register_style( 'qeema-hero-section', $plugin_url . 'assets/css/hero-section.css', array(), $version );
	wp_register_script( 'qeema-hero-section', $plugin_url . 'assets/js/hero-section.js', array( 'jquery' ), $version, true );

	wp_register_style( 'qeema-stats-counter', $plugin_url . 'assets/css/stats-counter.css', array(), $version );
	wp_register_script( 'qeema-stats-counter', $plugin_url . 'assets/js/stats-counter.js', array( 'jquery' ), $version, true );

	wp_register_style( 'qeema-feature-grid', $plugin_url . 'assets/css/feature-grid.css', array(), $version );
}
add_action( 'wp_enqueue_scripts', 'qeema_register_widget_assets' );
add_action( 'elementor/editor/before_enqueue_scripts', 'qeema_register_widget_assets' );

function qeema_register_widgets( $widgets_manager ) {
	require_once __DIR__ . '/../widgets/hero-section-widget.php';
	require_once __DIR__ . '/../widgets/stats-counter-widget.php';
	require_once __DIR__ . '/../widgets/feature-grid-widget.php';

	$widgets_manager->register( new \Qeema_Hero_Section_Widget() );
	$widgets_manager->register( new \Qeema_Stats_Counter_Widget() );
	$widgets_manager->register( new \Qeema_Feature_Grid_Widget() );
}
add_action( 'elementor/widgets/register', 'qeema_register_widgets' );
