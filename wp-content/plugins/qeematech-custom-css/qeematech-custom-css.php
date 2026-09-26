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

	// PERF-1: this file was found ~11% smaller (whitespace/comments only —
	// no rule changes) once minified, and it's ~50KB unminified, so that's
	// real render-blocking bytes on every page load. A .min.css sibling is
	// (re)generated only when style.css's own mtime moves past it — same
	// "regenerate on source change" shape as filemtime() cache-busting
	// above, just applied to the file's contents instead of its version
	// query string. Falls back to serving the unminified file untouched if
	// the minified copy can't be written (e.g. read-only filesystem) or
	// minification produces something obviously broken (empty output).
	$min_path = __DIR__ . '/assets/css/style.min.css';
	$src_mtime = filemtime( $path );

	if ( ! file_exists( $min_path ) || filemtime( $min_path ) < $src_mtime ) {
		$minified = qeema_minify_css( file_get_contents( $path ) );
		if ( $minified && strlen( $minified ) > 100 ) {
			file_put_contents( $min_path, $minified );
			// Match the minified file's mtime to the source so the staleness
			// check above is exact even if the filesystem's write-time
			// granularity differs from $src_mtime.
			touch( $min_path, $src_mtime );
		}
	}

	$serve_min = file_exists( $min_path ) && filemtime( $min_path ) >= $src_mtime;

	wp_enqueue_style(
		'qeematech-custom-css',
		plugin_dir_url( __FILE__ ) . 'assets/css/' . ( $serve_min ? 'style.min.css' : 'style.css' ),
		array(),
		$serve_min ? filemtime( $min_path ) : $src_mtime
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

	// Optional preloader: keep markup and script gated together. Disabled by
	// default so content never waits behind a JavaScript-controlled overlay.
	if ( qeema_preloader_enabled() ) {
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
}
add_action( 'wp_enqueue_scripts', 'qeema_custom_css_enqueue', 999 );
add_action( 'elementor/preview/enqueue_styles', 'qeema_custom_css_enqueue', 999 );

/**
 * Deliberately conservative regex minifier — this stylesheet has no url()
 * data-URIs or multi-word quoted content strings (checked directly), so
 * blindly collapsing whitespace is safe here. Strips comments, collapses
 * runs of whitespace to a single space, removes the space around structural
 * punctuation ({ } : ; , > ~ +), and drops the now-redundant last
 * declaration's trailing semicolon in each rule block.
 */
function qeema_minify_css( $css ) {
	// Strip /* ... */ comments (non-greedy, spans newlines).
	$css = preg_replace( '#/\*.*?\*/#s', '', $css );
	// Collapse all whitespace runs (including newlines) to a single space.
	$css = preg_replace( '/\s+/', ' ', $css );
	// Drop the space around structural punctuation.
	$css = preg_replace( '/\s*([{}:;,>~+])\s*/', '$1', $css );
	// The last declaration in a block doesn't need its trailing semicolon.
	$css = str_replace( ';}', '}', $css );
	return trim( $css );
}

/**
 * The Rubik font stylesheet was a plain wp_enqueue_style, making it a
 * render-blocking request to a third-party domain on every page. Printed
 * directly (instead of through wp_enqueue_style) using the standard
 * preload+swap pattern: the browser fetches it at high priority without
 * blocking rendering, then swaps it to an active stylesheet once it lands.
 * The preconnect hints cut the connection-setup latency for that request.
 */
function qeema_preload_google_font() {
	$font_url = 'https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300;400;500;600;700;800&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap';
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
 * Optional homepage "Mark Assemble" preloader: the real QT
 * mark splits into its 4 quadrants and flies together from the corners
 * (see .qeema-preloader__shard in style.css), instead of a generic spinner.
 * Disabled by default; opt in with qeema_enable_preloader. The actual
 * minimum-display/fade-out timing lives entirely in preloader.js.
 */
function qeema_preloader_enabled() {
	// The overlay delayed visible content and depended on delayed JavaScript.
	return is_front_page() && (bool) apply_filters( 'qeema_enable_preloader', false );
}

function qeema_print_preloader_markup() {
	// is_front_page() isn't reliably populated this early on every hook, but
	// wp_body_open fires well after the main query is resolved, so it's safe
	// here. Sitewide preloader was overkill — only the homepage needs it.
	if ( ! qeema_preloader_enabled() ) {
		return;
	}

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

/**
 * Elementor's "header-footer" full-width page template bypasses the theme's
 * normal template-parts/single.php entirely — which is the only place
 * <main id="content"> normally gets printed — so any Elementor-built page
 * (confirmed: the homepage) ends up with no <main> landmark at all, and the
 * theme's skip-link (href="#content" in hello-elementor/header.php) points
 * at an element that doesn't exist. These two hooks, fired by Elementor core
 * right before/after it renders the page content on that template, wrap the
 * content in a real <main id="content"> so the landmark exists and the
 * skip-link target actually resolves.
 */
add_action( 'elementor/page_templates/header-footer/before_content', 'qeema_open_main_landmark' );
function qeema_open_main_landmark() {
	echo '<main id="content" class="qeema-main" role="main">';
}

add_action( 'elementor/page_templates/header-footer/after_content', 'qeema_close_main_landmark' );
function qeema_close_main_landmark() {
	echo '</main>';
}

/**
 * Singular posts (blog posts, portfolio items) don't go through the
 * header-footer page template above at all - Elementor Pro's Theme Builder
 * renders them via its own location system (locations-manager.php's
 * do_location('single')), which fires elementor/theme/before_do_single and
 * elementor/theme/after_do_single around just the main-content area. Same
 * missing-<main>-landmark problem, same fix, different hook pair.
 */
add_action( 'elementor/theme/before_do_single', 'qeema_open_main_landmark' );
add_action( 'elementor/theme/after_do_single', 'qeema_close_main_landmark' );
