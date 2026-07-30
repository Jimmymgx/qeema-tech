<?php
/**
 * Plugin Name: Qeematech Custom CSS
 * Description: Loads Elementor style overrides for qeematech.net after Elementor's own CSS.
 * Version: 0.1.0
 * Author: Qeematech
 * Text Domain: qeematech-custom-css
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single consolidated stylesheet for every qeematech-elementor-widgets
 * component. Enqueued at priority 999 so it loads after Elementor's own
 * kit/global CSS — that's what actually fixes rules getting silently
 * overridden by Elementor's global styles at equal specificity (found while
 * building the Feature Grid widget: an inline !important was needed only
 * because this stylesheet was loading too early relative to Elementor's).
 * filemtime() cache-busts so edits show immediately without a version bump.
 */
function qeema_custom_css_enqueue() {
	wp_enqueue_style(
		'qeema-google-font-rubik',
		'https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap',
		array(),
		null
	);

	$path = __DIR__ . '/assets/css/style.css';
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_style(
		'qeematech-custom-css',
		plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
		array( 'qeema-google-font-rubik' ),
		filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'qeema_custom_css_enqueue', 999 );
add_action( 'elementor/preview/enqueue_styles', 'qeema_custom_css_enqueue', 999 );
