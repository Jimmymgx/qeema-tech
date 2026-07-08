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

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
