<?php
define( 'WP_CACHE', true );

define( 'DB_NAME', 'qeematech_new' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'ZWGCVaw3O7DXQKSF9YFCH3gKgtJdYSDSb4+k4uSzaHuvTG9jKhKhVh3qP1coJ9rR' );
define( 'SECURE_AUTH_KEY',  'axPvkcx/jS73FdWer/+XET7QdksEGIPGSKKDHgEmS68eeLIwCtNZQJjg330BTILa' );
define( 'LOGGED_IN_KEY',    '+Hb0SsHpx3rZA3Y+CfLwnYy9/4MmXIYXdHrU0/DPKZ8d3y0YpAZlczb+Bc0/r1hn' );
define( 'NONCE_KEY',        '798Xk4rYpbz87rG4wmTTB0Tt+s+vY+/jEyl/o0RweqXtxFMM0JRV0sJa3DiYl4k8' );
define( 'AUTH_SALT',        'H/93f/vrsKz4vaAsNhM7elDzHpBFBiZwRvTqy/bZ1kzBuxrEnHlzS7Mmqq99fkqc' );
define( 'SECURE_AUTH_SALT', 'uog9g53RCHSRQE2d+Nlpi08xNV23PG5tQQvatw5EEYz+pGutjE995t1d2vY+XIfU' );
define( 'LOGGED_IN_SALT',   'umnKYE85ix4pFVMlZLU72dPnhNsFRVstZ+xLFbWrJ1H+81Wmh5xkkK3x7arsx5J1' );
define( 'NONCE_SALT',       'MjlJ5/kAyAI83eFTETwURl4M4QqMfyJoXWL5JhRtioF8d0F70gs7t19mCMf6/bc/' );

/**
 * New table prefix, per the rebuild plan (never `wp_`).
 */
$table_prefix = 'qeema_';

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/**
 * Blocks the wp-admin Theme/Plugin file editor. A compromised admin account
 * could otherwise use it for instant code execution — cheap to close off
 * regardless of how the account got compromised.
 */
define( 'DISALLOW_FILE_EDIT', true );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/**
 * Behind a tunnel (ngrok, VS Code devtunnel, etc.), the tunnel terminates
 * HTTPS and forwards to this Apache instance over plain HTTP — so
 * WordPress's own is_ssl()/HTTP_HOST-based request detection sees "http" +
 * "localhost" while the real public request was "https" + the tunnel's
 * hostname. That mismatch made redirect_canonical() 301 every request to
 * "itself" forever (WP_HOME said https, but WP core's own scheme check
 * still said http, so it never agreed the request already matched). Fixing
 * $_SERVER directly — before wp-settings.php boots — makes ALL of WP core
 * (not just the WP_HOME/WP_SITEURL constants below) treat the request
 * consistently. Confirmed against a real ngrok tunnel: it reliably sends
 * X-Forwarded-Host + X-Forwarded-Proto; HTTP_HOST itself stays "localhost".
 * No-op when there's no forwarding proxy in front (plain localhost access).
 */
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	$_SERVER['HTTPS'] = 'on';
}
if ( ! empty( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

$qeema_host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$qeema_scheme = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) ? 'https' : 'http';
define( 'WP_HOME', "$qeema_scheme://$qeema_host/qeematech-new" );
define( 'WP_SITEURL', "$qeema_scheme://$qeema_host/qeematech-new" );

require_once ABSPATH . 'wp-settings.php';
