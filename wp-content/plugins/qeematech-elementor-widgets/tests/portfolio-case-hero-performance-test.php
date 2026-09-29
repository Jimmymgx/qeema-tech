<?php
$widget = file_get_contents( dirname( __DIR__ ) . '/widgets/portfolio-case-hero-widget.php' );
$failures = array();

function qeema_cwv_check( $condition, $message ) {
	global $failures;
	if ( ! $condition ) {
		$failures[] = $message;
	}
}

qeema_cwv_check( 2 === substr_count( $widget, "'data-no-lazy'  => '1'" ), 'Both app and website hero images must opt out of LiteSpeed lazy loading.' );
qeema_cwv_check( 2 === substr_count( $widget, "'loading'       => 'eager'" ), 'Both hero variants must load eagerly.' );
qeema_cwv_check( 2 === substr_count( $widget, "'fetchpriority' => 'high'" ), 'Both hero variants must retain high fetch priority.' );
qeema_cwv_check( false === strpos( $widget, 'qeema-cs-hero__copy qeema-reveal' ), 'Above-the-fold copy must not wait for scroll reveal.' );
qeema_cwv_check( false === strpos( $widget, 'qeema-cs-hero__visual qeema-reveal' ), 'The LCP visual must not wait for scroll reveal.' );
qeema_cwv_check( false !== strpos( $widget, "'sizes'         => '(max-width:980px) 70vw, 320px'" ), 'Responsive sizing for app artwork must be retained.' );

if ( $failures ) {
	fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
	exit( 1 );
}

echo "6 portfolio hero performance checks passed\n";
