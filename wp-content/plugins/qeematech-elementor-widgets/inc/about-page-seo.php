<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Organization + AboutPage JSON-LD for the About Us page. Rank Math already
 * emits its own per-page WebPage/Article graph; this is deliberately scoped
 * to just this one page and only adds an Organization node (name/url/logo),
 * which Rank Math's default graph doesn't include unless configured in its
 * "Local SEO" module — additive, not a duplicate of what Rank Math outputs.
 * Only real, already-verified content is used here (no invented founding
 * date, address, or social links).
 */
function qeema_about_page_schema() {
	if ( ! is_page( 'about-us' ) ) {
		return;
	}

	$logo_url = trailingslashit( wp_upload_dir()['baseurl'] ) . '2026/08/qt-icon-only.png';

	$graph = array(
		'@context' => 'https://schema.org',
		'@graph'   => array(
			array(
				'@type' => 'Organization',
				'@id'   => home_url( '/#organization' ),
				'name'  => 'قيمة تك',
				'url'   => home_url( '/' ),
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => $logo_url,
				),
			),
			array(
				'@type'      => 'AboutPage',
				'@id'        => get_permalink() . '#aboutpage',
				'url'        => get_permalink(),
				'name'       => get_the_title(),
				'description' => 'قيمة تك شركة متخصصة في تطوير المواقع والتطبيقات والأنظمة الذكية، نساعد الشركات على تحويل أفكارها إلى منتجات رقمية حقيقية.',
				'isPartOf'   => array( '@id' => home_url( '/#website' ) ),
				'about'      => array( '@id' => home_url( '/#organization' ) ),
				'mainEntity' => array( '@id' => home_url( '/#organization' ) ),
			),
		),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- structured, non-user-controlled data.
}
add_action( 'wp_head', 'qeema_about_page_schema' );
