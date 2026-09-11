<?php
/**
 * Plugin Name: Qeema subdirectory URL fix
 * Description: Rewrites root-relative href/action/src starting with / (but not //)
 *              so they work when WordPress lives in /qeema-tech/. Never double-prefixes.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path WordPress is installed under (e.g. "/qeema-tech"), or "".
 *
 * @return string
 */
function qeema_home_path() {
	static $path = null;
	if ( null !== $path ) {
		return $path;
	}
	$parsed = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$path   = untrailingslashit( is_string( $parsed ) ? $parsed : '' );
	if ( '/' === $path ) {
		$path = '';
	}
	return $path;
}

/**
 * Collapse accidental /qeema-tech/qeema-tech/... stacks, then ensure a single home prefix.
 *
 * @param string $url Absolute or root-relative URL.
 * @return string
 */
function qeema_fix_root_relative_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}
	if ( ! str_starts_with( $url, '/' ) || str_starts_with( $url, '//' ) ) {
		return $url;
	}

	$home_path = qeema_home_path();
	if ( '' === $home_path ) {
		return $url;
	}

	// Collapse repeated home-path segments produced by earlier buggy rewrites.
	$pattern = '#' . preg_quote( $home_path, '#' ) . '(?:' . preg_quote( $home_path, '#' ) . ')+#';
	$url     = preg_replace( $pattern, $home_path, $url );

	if ( $url === $home_path || str_starts_with( $url, $home_path . '/' ) ) {
		return $url;
	}

	return $home_path . $url;
}

/**
 * Rewrite root-relative URLs inside HTML attributes (full attribute value).
 *
 * @param string $html HTML fragment.
 * @return string
 */
function qeema_fix_root_relative_html( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}

	return preg_replace_callback(
		'#\b(href|action|src|formaction|data-url|data-link)=([\'"])(/[^\'"]*)\2#i',
		static function ( $m ) {
			$fixed = qeema_fix_root_relative_url( $m[3] );
			return $m[1] . '=' . $m[2] . $fixed . $m[2];
		},
		$html
	);
}

add_filter( 'the_content', 'qeema_fix_root_relative_html', 99 );
add_filter( 'widget_text', 'qeema_fix_root_relative_html', 99 );
add_filter( 'elementor/frontend/the_content', 'qeema_fix_root_relative_html', 99 );
add_filter( 'elementor/widget/render_content', 'qeema_fix_root_relative_html', 99 );

add_filter(
	'nav_menu_link_attributes',
	static function ( $atts ) {
		if ( ! empty( $atts['href'] ) ) {
			$atts['href'] = qeema_fix_root_relative_url( $atts['href'] );
		}
		return $atts;
	},
	20
);
