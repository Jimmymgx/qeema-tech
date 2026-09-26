<?php
/** Exact historical redirects and sitemap defaults for the clean rebuild. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Only fill missing settings; retain explicit administrator exclusions. */
function qeema_recovery_sitemap_defaults( $options ) {
	$options = is_array( $options ) ? $options : array();
	foreach ( array( 'portfolio', 'live-apps' ) as $type ) {
		$key = 'pt_' . $type . '_sitemap';
		if ( ! array_key_exists( $key, $options ) ) {
			$options[ $key ] = 'on';
		}
	}
	return $options;
}
add_filter( 'option_rank-math-options-sitemap', 'qeema_recovery_sitemap_defaults' );
add_filter( 'default_option_rank-math-options-sitemap', 'qeema_recovery_sitemap_defaults' );

/** Remove only the actual installation prefix, preserving exact path identity. */
function qeema_recovery_request_path( $request_uri ) {
	$path = wp_parse_url( $request_uri, PHP_URL_PATH );
	if ( ! is_string( $path ) || '' === $path || '/' !== $path[0] ) {
		return '';
	}
	// Encoded separators must not turn an unrelated URL into a known alias.
	if ( preg_match( '/%(?:2f|5c|00)/i', $path ) || false !== strpos( $path, '\\' ) ) {
		return '';
	}
	$path = rawurldecode( $path );
	$base = rtrim( rawurldecode( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ), '/' );
	if ( '' !== $base ) {
		if ( 0 !== strpos( $path, $base . '/' ) ) {
			return '';
		}
		$path = substr( $path, strlen( $base ) );
	}
	return '/' . trim( $path, '/' ) . '/';
}

/** Resolve a verified alias to a still-published post; never use database IDs. */
function qeema_recovery_redirect_target( $path ) {
	static $map = null;
	if ( null === $map ) {
		$map = require __DIR__ . '/seo-legacy-redirects.php';
	}
	if ( ! isset( $map[ $path ] ) ) {
		return '';
	}
	$entry = $map[ $path ];
	$post = get_page_by_path( $entry['slug'], OBJECT, $entry['type'] );
	if ( ! $post || 'publish' !== $post->post_status || '' !== $post->post_password || ! is_post_type_viewable( $post->post_type ) ) {
		return '';
	}
	$robots = get_post_meta( $post->ID, 'rank_math_robots', true );
	if ( is_array( $robots ) && in_array( 'noindex', $robots, true ) ) {
		return '';
	}
	$target = get_permalink( $post );
	if ( ! $target || wp_parse_url( $target, PHP_URL_HOST ) !== wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ) {
		return '';
	}
	// Do not redirect a source to itself after permalink filters run.
	return qeema_recovery_request_path( (string) wp_parse_url( $target, PHP_URL_PATH ) ) === $path ? '' : $target;
}

function qeema_recovery_redirect_legacy_url() {
	if ( is_admin() || ! is_404() || ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', array( 'GET', 'HEAD' ), true ) ) {
		return;
	}
	// Preview/search/feed/pagination requests are not aliases of article pages.
	foreach ( array( 'preview', 's', 'feed', 'paged', 'page', 'p', 'page_id', 'rest_route' ) as $key ) {
		if ( isset( $_GET[ $key ] ) ) {
			return;
		}
	}
	$path = qeema_recovery_request_path( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	$target = qeema_recovery_redirect_target( $path );
	if ( $target ) {
		// Preserve only campaign attribution, never arbitrary query destinations.
		$tracking = array();
		foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'gad_source', 'gad_campaignid', 'gbraid', 'wbraid' ) as $key ) {
			if ( isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ) {
				$tracking[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
			}
		}
		if ( $tracking ) {
			$target = add_query_arg( $tracking, $target );
		}
		if ( wp_safe_redirect( $target, 301, 'Qeema migration recovery' ) ) {
			exit;
		}
	}
}
add_action( 'template_redirect', 'qeema_recovery_redirect_legacy_url', 1 );

/** Run once in wp-admin after a code merge, not on every public request. */
function qeema_recovery_invalidate_sitemap_cache() {
	$version = '2026-09-26-1';
	if ( ! current_user_can( 'manage_options' ) || get_option( 'qeema_seo_recovery_version' ) === $version || ! class_exists( '\RankMath\Sitemap\Cache' ) ) {
		return;
	}
	\RankMath\Sitemap\Cache::invalidate_storage();
	update_option( 'qeema_seo_recovery_version', $version, false );
}
add_action( 'admin_init', 'qeema_recovery_invalidate_sitemap_cache' );
