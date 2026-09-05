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
	// The Rubik font stylesheet is loaded non-blocking via qeema_preload_google_font()
	// below instead of a normal wp_enqueue_style — see that function for why.
	$path = __DIR__ . '/assets/css/style.css';
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_style(
		'qeematech-custom-css',
		plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
		array(),
		filemtime( $path )
	);

	$cursor_js_path = __DIR__ . '/assets/js/cursor.js';
	if ( file_exists( $cursor_js_path ) ) {
		wp_enqueue_script(
			'qeematech-cursor',
			plugin_dir_url( __FILE__ ) . 'assets/js/cursor.js',
			array(),
			filemtime( $cursor_js_path ),
			true
		);
	}

	$preloader_js_path = __DIR__ . '/assets/js/preloader.js';
	if ( file_exists( $preloader_js_path ) ) {
		wp_enqueue_script(
			'qeematech-preloader',
			plugin_dir_url( __FILE__ ) . 'assets/js/preloader.js',
			array(),
			filemtime( $preloader_js_path ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'qeema_custom_css_enqueue', 999 );
add_action( 'elementor/preview/enqueue_styles', 'qeema_custom_css_enqueue', 999 );

/**
 * The Rubik font stylesheet was a plain wp_enqueue_style, making it a
 * render-blocking request to a third-party domain on every page. Printed
 * directly (instead of through wp_enqueue_style) using the standard
 * preload+swap pattern: the browser fetches it at high priority without
 * blocking rendering, then swaps it to an active stylesheet once it lands.
 * The preconnect hints cut the connection-setup latency for that request.
 */
function qeema_preload_google_font() {
	$font_url = 'https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap';
	?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preload" as="style" href="<?php echo esc_url( $font_url ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( $font_url ); ?>" media="print" onload="this.media='all'">
	<noscript><link rel="stylesheet" href="<?php echo esc_url( $font_url ); ?>"></noscript>
	<?php
}
add_action( 'wp_head', 'qeema_preload_google_font', 1 );

/**
 * Sitewide "Mark Assemble" preloader, shown on every page load: the real QT
 * mark splits into its 4 quadrants and flies together from the corners
 * (see .qeema-preloader__shard in style.css), instead of a generic spinner.
 * Printed at wp_body_open like the background canvas above; the actual
 * minimum-display/fade-out timing lives entirely in preloader.js.
 */
function qeema_print_preloader_markup() {
	$icon_url = trailingslashit( wp_upload_dir()['baseurl'] ) . '2026/08/qt-icon-only.png';
	?>
	<div id="qeema-preloader" role="status" aria-live="polite">
		<span class="qeema-preloader__sr-text">جاري تحميل الموقع</span>
		<div class="qeema-preloader__assemble" aria-hidden="true">
			<img class="qeema-preloader__shard tl" src="<?php echo esc_url( $icon_url ); ?>" alt="">
			<img class="qeema-preloader__shard tr" src="<?php echo esc_url( $icon_url ); ?>" alt="">
			<img class="qeema-preloader__shard bl" src="<?php echo esc_url( $icon_url ); ?>" alt="">
			<img class="qeema-preloader__shard br" src="<?php echo esc_url( $icon_url ); ?>" alt="">
		</div>
	</div>
	<?php
}
add_action( 'wp_body_open', 'qeema_print_preloader_markup' );
