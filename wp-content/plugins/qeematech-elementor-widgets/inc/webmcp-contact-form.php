<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Agentic-web readiness: annotate the Contact page's Elementor Pro form with
 * WebMCP's declarative attributes (toolname/tooldescription/toolparamdescription)
 * so AI agents can discover and fill it out reliably instead of guessing at
 * field purpose from labels. WebMCP is still an experimental/proposed browser
 * API - these attributes are inert (and harmless) in every browser that
 * doesn't implement it yet. Scoped to post 800 ("Contact us") only, since
 * that's the site's one real lead-generation form.
 */
function qeema_enqueue_webmcp_contact_form() {
	if ( ! is_page( 800 ) ) {
		return;
	}
	$plugin_url  = plugin_dir_url( __DIR__ );
	$plugin_path = plugin_dir_path( __DIR__ );
	$rel_path    = 'assets/js/webmcp-contact-form.js';
	$version     = file_exists( $plugin_path . $rel_path ) ? filemtime( $plugin_path . $rel_path ) : '0.1.0';

	wp_enqueue_script( 'qeema-webmcp-contact-form', $plugin_url . $rel_path, array( 'jquery' ), $version, true );
}
add_action( 'wp_enqueue_scripts', 'qeema_enqueue_webmcp_contact_form' );
