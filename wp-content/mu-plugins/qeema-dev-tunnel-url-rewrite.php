<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The WP_HOME/WP_SITEURL auto-detection in wp-config.php only fixes URLs
 * WordPress generates fresh at render time (enqueued script/style tags, nav
 * menu links, canonical tags). It can't fix URLs already baked as literal
 * strings into saved content — Elementor `_elementor_data` image/link
 * settings, the theme header logo `<img src>`, JSON-LD schema — since those
 * were written once (as `http://localhost/qeematech-new/...`) and aren't
 * recomputed on render. This rewrites those literal strings in the final
 * HTML output whenever the request's real host differs from localhost (i.e.
 * only when accessed through a forwarded/tunneled URL) — a no-op on
 * ordinary localhost requests.
 */
add_action( 'template_redirect', function () {
	if ( is_admin() ) {
		return;
	}

	$current_home = untrailingslashit( home_url() );
	if ( 'http://localhost/qeematech-new' === $current_home ) {
		return;
	}

	ob_start( function ( $html ) use ( $current_home ) {
		return str_replace( 'http://localhost/qeematech-new', $current_home, $html );
	} );
}, 0 );
