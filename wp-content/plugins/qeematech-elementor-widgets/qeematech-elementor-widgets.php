<?php
/**
 * Plugin Name: Qeematech Elementor Widgets
 * Description: Engine plugin — custom Elementor widgets, CPTs, AJAX handlers, security hardening, and performance for qeematech.net.
 * Version: 0.1.0
 * Author: Qeematech
 * Text Domain: qeematech-elementor-widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/inc/cpt-acf-registration.php';
require_once __DIR__ . '/inc/widgets-registration.php';
require_once __DIR__ . '/inc/about-page-seo.php';
require_once __DIR__ . '/inc/ga4-tracking.php';
require_once __DIR__ . '/inc/create-single-post-template.php';
require_once __DIR__ . '/inc/create-single-portfolio-template.php';
require_once __DIR__ . '/inc/create-blog-archive-page.php';
require_once __DIR__ . '/inc/create-works-archive-page.php';
require_once __DIR__ . '/inc/ajax-archive-endpoints.php';
require_once __DIR__ . '/inc/create-portfolio-category-pages.php';
require_once __DIR__ . '/inc/create-live-app-page.php';
require_once __DIR__ . '/inc/create-testimonials-page.php';
require_once __DIR__ . '/inc/create-clients-page.php';
require_once __DIR__ . '/inc/create-bank-info-page.php';
require_once __DIR__ . '/inc/create-thank-you-page.php';
require_once __DIR__ . '/inc/create-contracting-landing-page.php';
require_once __DIR__ . '/inc/create-live-apps-home-carousel.php';
/**
 * The live-content import tools (Tools → Import Live Content + their AJAX
 * batch handlers) are only needed while migrating content from qeematech.net.
 * They're properly nonce + manage_options gated, but there's no reason for
 * this much extra code/surface to stay permanently loaded once migration is
 * done — off by default, flip back on in wp-config.php only when another
 * import run is actually needed:
 *   define( 'QEEMA_ENABLE_LIVE_IMPORT_TOOLS', true );
 */
if ( defined( 'QEEMA_ENABLE_LIVE_IMPORT_TOOLS' ) && QEEMA_ENABLE_LIVE_IMPORT_TOOLS ) {
	require_once __DIR__ . '/inc/import-portfolio-endpoint.php';
	require_once __DIR__ . '/inc/import-blog-endpoint.php';
	require_once __DIR__ . '/inc/import-live-apps-endpoint.php';
	require_once __DIR__ . '/inc/import-testimonials-endpoint.php';
	require_once __DIR__ . '/inc/import-clients-endpoint.php';
	require_once __DIR__ . '/inc/live-import-admin-page.php';
}
