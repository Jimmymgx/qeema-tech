<?php
define( 'ABSPATH', __DIR__ );
function add_filter() {}
require dirname( __DIR__ ) . '/inc/structured-data-content.php';
$tests = array(
	'valid standard' => array( '<script type="application/ld+json">{"@type":"FAQPage"}</script>', true ),
	'valid spaced' => array( '<script type = "application/ld+json">{"@type":"FAQPage"}</script>', true ),
	'valid single quoted' => array( "<script type = 'application/ld+json'>{\"name\":\"valid\"}</script>", true ),
	'valid embedded html' => array( '<script type="application/ld+json">{"text":"<p>Answer</p>"}</script>', true ),
	'missing comma' => array( '<script type = "application/ld+json">{"name":"x" "text":"y"}</script>', false ),
	'invalid unquoted mime' => array( '<script type=application/ld+json>{broken}</script>', false ),
	'ordinary javascript' => array( '<script type="text/javascript">not JSON;</script>', true ),
	'data-type is not type' => array( '<script data-type="application/ld+json">not JSON;</script>', true ),
	'quoted greater-than' => array( '<script data-example=">" type="application/ld+json">{broken}</script>', false ),
	'uppercase attributes' => array( '<SCRIPT TYPE = "APPLICATION/LD+JSON">{broken}</SCRIPT>', false ),
	'visible FAQ' => array( '<h2>FAQ</h2><p>Answer with {braces}.</p>', true ),
);
foreach ( $tests as $label => $case ) {
	$input = '<p>Before</p>' . $case[0] . '<p>After</p>';
	$expected = '<p>Before</p>' . ( $case[1] ? $case[0] : '' ) . '<p>After</p>';
	if ( qeema_remove_invalid_content_jsonld( $input ) !== $expected ) {
		fwrite( STDERR, 'FAIL: ' . $label . PHP_EOL ); exit( 1 );
	}
}
echo count( $tests ) . " structured-data checks passed\n";
