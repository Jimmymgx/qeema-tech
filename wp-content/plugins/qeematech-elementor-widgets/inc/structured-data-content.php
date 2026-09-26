<?php
/** Prevent malformed, manually embedded JSON-LD from reaching search engines. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function qeema_remove_invalid_content_jsonld( $content ) {
	if ( ! is_string( $content ) || false === stripos( $content, '<script' ) ) {
		return $content;
	}
	// Match script elements only, including quoted attributes containing >.
	// Do not rewrite page HTML, visible FAQ text, or Rank Math's head output.
	$pattern = '~<script\b((?:"[^"]*"|\'[^\']*\'|[^\'">])*)>(.*?)</script\s*>~is';
	$filtered = preg_replace_callback( $pattern, static function ( $match ) {
		if ( ! preg_match( '~(?:^|\s)type\s*=\s*(?:"application/ld\+json"|\'application/ld\+json\'|application/ld\+json(?=\s|$))~i', $match[1] ) ) {
			return $match[0];
		}
		json_decode( trim( $match[2] ) );
		return JSON_ERROR_NONE === json_last_error() ? $match[0] : '';
	}, $content );
	return null === $filtered ? $content : $filtered;
}

// After wpautop and shortcodes: those can introduce malformed legacy blocks.
add_filter( 'the_content', 'qeema_remove_invalid_content_jsonld', 99 );
