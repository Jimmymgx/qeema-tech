<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PERF-5: jquery-core/jquery-migrate load render-blocking in <head>. This
 * plugin's own frontend scripts (site-header.js, the carousel/hero JS files,
 * cursor.js) already only touch jQuery inside DOMContentLoaded/elementor
 * init handlers, so they'd have been safe on their own.
 *
 * TESTED AND CONFIRMED BROKEN (2026-09-24): enabling this constant throws
 * "ReferenceError: jQuery is not defined" from Elementor core's own
 * frontend-modules.min.js, from wp-includes' jquery-ui core.min.js, and a
 * cascading "elementorModules is not defined" from Elementor Pro's
 * frontend.min.js — all three load non-deferred in the footer and reference
 * jQuery synchronously at parse time, which happens *during* HTML parsing
 * (before a deferred <head> script has executed). This breaks Elementor's
 * entire frontend runtime (menus, sliders, motion effects, popups — anything
 * Elementor/Elementor Pro power). Do not enable QEEMA_DEFER_JQUERY without
 * also deferring every Elementor/jQuery-UI-dependent script in lockstep
 * (a much larger, riskier change than this task's scope) or moving to a
 * different technique entirely (e.g. loading a bundled/self-hosted jQuery
 * earlier via a blocking preload). Left in place, default off, as a record
 * of what was tried — do not flip this on as-is.
 */
function qeema_defer_jquery_tag( $tag, $handle ) {
	if ( is_admin() ) {
		return $tag;
	}
	if ( ! in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate' ), true ) ) {
		return $tag;
	}
	if ( false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}
	return str_replace( ' src=', ' defer src=', $tag );
}
if ( defined( 'QEEMA_DEFER_JQUERY' ) && QEEMA_DEFER_JQUERY ) {
	add_filter( 'script_loader_tag', 'qeema_defer_jquery_tag', 10, 2 );
}
