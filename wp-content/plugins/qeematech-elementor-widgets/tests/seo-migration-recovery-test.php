<?php
define( 'ABSPATH', __DIR__ );
define( 'OBJECT', 'OBJECT' );
function add_filter() {}
function add_action() {}
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function home_url( $path = '' ) { return $GLOBALS['test_home'] . $path; }
function get_page_by_path( $slug, $format, $type ) { return $GLOBALS['test_post']; }
function is_post_type_viewable( $type ) { return $GLOBALS['test_viewable']; }
function get_post_meta() { return $GLOBALS['test_robots']; }
function get_permalink( $post ) { return $GLOBALS['test_target']; }
require dirname( __DIR__ ) . '/inc/seo-migration-recovery.php';
$checks = 0;
function same( $expected, $actual, $label ) {
	global $checks;
	++$checks;
	if ( $expected !== $actual ) { fwrite( STDERR, "FAIL: $label\n" ); exit( 1 ); }
}
$GLOBALS['test_home'] = 'http://localhost/qeematech-new';
same( '/portfolio/greenwing/', qeema_recovery_request_path( '/qeematech-new/portfolio/greenwing/?utm_source=test' ), 'subdirectory query removed' );
same( '', qeema_recovery_request_path( '/qeematech-newer/portfolio/greenwing/' ), 'prefix boundary' );
same( '', qeema_recovery_request_path( '/qeematech-new/portfolio%2Fgreenwing/' ), 'encoded slash rejected' );
same( '/تجربة/', qeema_recovery_request_path( '/qeematech-new/%D8%AA%D8%AC%D8%B1%D8%A8%D8%A9/' ), 'Arabic decoding' );
$GLOBALS['test_home'] = 'https://www.qeematech.net';
same( '/portfolio/greenwing/', qeema_recovery_request_path( '/portfolio/greenwing' ), 'production root' );
$options = qeema_recovery_sitemap_defaults( array( 'pt_portfolio_sitemap' => 'off', 'exclude_posts' => '42' ) );
same( 'off', $options['pt_portfolio_sitemap'], 'explicit sitemap exclusion preserved' );
same( 'on', $options['pt_live-apps_sitemap'], 'missing app default restored' );
same( '42', $options['exclude_posts'], 'other options preserved' );
same( 'on', qeema_recovery_sitemap_defaults( false )['pt_portfolio_sitemap'], 'missing option handled' );
$GLOBALS['test_post'] = (object) array( 'ID' => 1, 'post_status' => 'publish', 'post_password' => '', 'post_type' => 'portfolio' );
$GLOBALS['test_viewable'] = true;
$GLOBALS['test_robots'] = array( 'index' );
$GLOBALS['test_target'] = 'https://www.qeematech.net/portfolio/green-wing/';
same( $GLOBALS['test_target'], qeema_recovery_redirect_target( '/portfolio/greenwing/' ), 'verified alias resolves' );
same( '', qeema_recovery_redirect_target( '/portfolio/not-a-real-project/' ), 'unknown alias untouched' );
same( '', qeema_recovery_redirect_target( '/portfolio/greenwing/page/121/' ), 'no prefix or pagination redirect' );
$GLOBALS['test_post']->post_status = 'draft';
same( '', qeema_recovery_redirect_target( '/portfolio/greenwing/' ), 'draft cannot be target' );
$GLOBALS['test_post']->post_status = 'publish';
$GLOBALS['test_post']->post_password = 'protected';
same( '', qeema_recovery_redirect_target( '/portfolio/greenwing/' ), 'password protection honored' );
$GLOBALS['test_post']->post_password = '';
$GLOBALS['test_robots'] = array( 'noindex' );
same( '', qeema_recovery_redirect_target( '/portfolio/greenwing/' ), 'noindex target skipped' );
$GLOBALS['test_robots'] = array();
$GLOBALS['test_target'] = 'https://unrelated.example/';
same( '', qeema_recovery_redirect_target( '/portfolio/greenwing/' ), 'external redirect rejected' );
$GLOBALS['test_target'] = 'https://www.qeematech.net/portfolio/greenwing/';
same( '', qeema_recovery_redirect_target( '/portfolio/greenwing/' ), 'self redirect rejected' );
echo "$checks checks passed\n";
