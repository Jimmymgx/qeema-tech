<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects the visitor's dial code from their IP, to pre-select the Contact
 * page's country dropdown (see country-dial-codes.php for the option list
 * and contact-country-detect.js for the front-end that calls this). Fails
 * silently on any error — the form must work fine with nothing pre-selected
 * if detection isn't possible (no API key, offline, rate-limited, etc.).
 */
function qeema_detect_visitor_ip() {
	$candidates = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	foreach ( $candidates as $key ) {
		if ( empty( $_SERVER[ $key ] ) ) {
			continue;
		}
		$value = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
		$ip    = trim( explode( ',', $value )[0] );
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}
	}
	return '';
}

/**
 * Tries ipapi.co first (gives the dial code directly). If it's down, rate-
 * limited, or errors (its free tier has no guaranteed uptime/quota), falls
 * back to ip-api.com, mapping its ISO country code to a dial code via our
 * own country-dial-codes.php list — keeping the result consistent with what
 * actually exists in the dropdown either way.
 */
function qeema_detect_country_dial_code() {
	$ip = qeema_detect_visitor_ip();
	if ( ! $ip ) {
		return null;
	}

	$response = wp_remote_get( 'https://ipapi.co/' . rawurlencode( $ip ) . '/json/', array( 'timeout' => 3 ) );
	if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! empty( $body['country_calling_code'] ) && empty( $body['error'] ) ) {
			return $body['country_calling_code'];
		}
	}

	$fallback = wp_remote_get( 'http://ip-api.com/json/' . rawurlencode( $ip ) . '?fields=status,countryCode', array( 'timeout' => 3 ) );
	if ( is_wp_error( $fallback ) || 200 !== (int) wp_remote_retrieve_response_code( $fallback ) ) {
		return null;
	}
	$fallback_body = json_decode( wp_remote_retrieve_body( $fallback ), true );
	if ( empty( $fallback_body['countryCode'] ) || 'success' !== ( $fallback_body['status'] ?? '' ) ) {
		return null;
	}

	foreach ( qeema_get_country_dial_codes() as $country ) {
		if ( $country['iso2'] === $fallback_body['countryCode'] ) {
			return $country['dial'];
		}
	}
	return null;
}

function qeema_ajax_detect_country() {
	wp_send_json_success( array( 'dial' => qeema_detect_country_dial_code() ) );
}
add_action( 'wp_ajax_qeema_detect_country', 'qeema_ajax_detect_country' );
add_action( 'wp_ajax_nopriv_qeema_detect_country', 'qeema_ajax_detect_country' );

/**
 * Only the Contact page (post 800) has a country dropdown to default —
 * no need to load this on every page.
 */
function qeema_enqueue_country_detect_script() {
	if ( ! is_page( 800 ) ) {
		return;
	}
	wp_enqueue_script(
		'qeema-contact-country-detect',
		plugins_url( '../assets/js/contact-country-detect.js', __FILE__ ),
		array(),
		'1.0.0',
		true
	);
	wp_localize_script( 'qeema-contact-country-detect', 'qeemaCountryDetect', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'qeema_enqueue_country_detect_script' );

/**
 * Custom-styled listbox for the Contact form's <select> fields — a plain
 * <select>'s OPEN dropdown list is rendered by the OS/browser and can't be
 * restyled with CSS, so this replaces it with a fully-styled one (see
 * assets/js/contact-modern-select.js and the .qeema-select__* CSS).
 */
function qeema_enqueue_modern_select_script() {
	if ( ! is_page( 800 ) ) {
		return;
	}
	wp_enqueue_script(
		'qeema-contact-modern-select',
		plugins_url( '../assets/js/contact-modern-select.js', __FILE__ ),
		array(),
		'1.0.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'qeema_enqueue_modern_select_script' );
