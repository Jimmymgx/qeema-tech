<?php
// Run with: php wp-cli.phar eval-file <this-file>
$map = require WP_PLUGIN_DIR . '/qeematech-elementor-widgets/inc/seo-legacy-redirects.php';
$failures = array();
foreach ( $map as $source => $entry ) {
	$target = qeema_recovery_redirect_target( $source );
	if ( ! $target ) {
		$failures[] = $source . ': no published indexable target';
		continue;
	}
	$id = url_to_postid( $target );
	if ( ! $id || get_post_status( $id ) !== 'publish' || get_post_type( $id ) !== $entry['type'] ) {
		$failures[] = $source . ': permalink did not resolve to intended post type';
	}
}
$settings = \RankMath\Helper::get_settings( 'sitemap' );
foreach ( array( 'portfolio', 'live-apps' ) as $type ) {
	if ( empty( $settings[ 'pt_' . $type . '_sitemap' ] ) ) $failures[] = $type . ': sitemap not enabled';
}
if ( $failures ) {
	foreach ( $failures as $failure ) WP_CLI::warning( $failure );
	WP_CLI::error( count( $failures ) . ' failures' );
}
WP_CLI::success( count( $map ) . ' redirect targets resolve to published posts; both sitemap types enabled.' );
