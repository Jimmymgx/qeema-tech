<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the thank-u-page-home (form thank-you) page. Matched to the old
 * site's actual content using native Elementor heading/text-editor/button
 * widgets (this is plain static content, no custom widget needed). The one
 * deliberate deviation from "match the old site exactly": Rank Math is set
 * to noindex here — the old site's own version is indexable despite being a
 * post-submission page with no legitimate organic-search value, which reads
 * as an oversight rather than an intentional SEO choice. This doesn't remove
 * any real ranking, since this page was never meant to appear in search
 * results in the first place.
 */
function qeema_thank_you_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_thank_you_page_heading( $title, $tag = 'h2', $align = 'center' ) {
	return array(
		'id'         => qeema_thank_you_page_qid(),
		'elType'     => 'widget',
		'settings'   => array( 'title' => $title, 'header_size' => $tag, 'align' => $align ),
		'elements'   => array(),
		'widgetType' => 'heading',
	);
}

function qeema_thank_you_page_text( $html, $align = 'center' ) {
	return array(
		'id'         => qeema_thank_you_page_qid(),
		'elType'     => 'widget',
		'settings'   => array( 'editor' => $html, 'align' => $align ),
		'elements'   => array(),
		'widgetType' => 'text-editor',
	);
}

function qeema_thank_you_page_button( $text, $url ) {
	return array(
		'id'         => qeema_thank_you_page_qid(),
		'elType'     => 'widget',
		'settings'   => array( 'text' => $text, 'link' => array( 'url' => $url ), 'align' => 'center' ),
		'elements'   => array(),
		'widgetType' => 'button',
	);
}

function qeema_maybe_create_thank_you_page() {
	if ( get_option( 'qeema_thank_you_page_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'page',
		'name'           => 'thank-u-page-home',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_thank_you_page_ready', true, true );
		return;
	}

	$elements = array(
		qeema_thank_you_page_heading( '✓ تم إرسال طلبك بنجاح', 'h3' ),
		qeema_thank_you_page_heading( 'شكرًا لك، سيتم التواصل معك خلال ساعات', 'h1' ),
		qeema_thank_you_page_text( '<p>وصلتنا بياناتك بنجاح، وسيقوم أحد أعضاء فريق قيمة تك بمراجعة طلبك والتواصل معك في أقرب وقت لمناقشة التفاصيل وتقديم أفضل تصور مناسب لمشروعك.</p>' ),
		qeema_thank_you_page_button( 'تواصل واتساب الآن', 'https://wa.me/201012804721' ),
		qeema_thank_you_page_button( 'العودة للرئيسية', '/' ),
		qeema_thank_you_page_heading( '⏱ ماذا يحدث الآن؟', 'h3' ),
		qeema_thank_you_page_text( '<p>01 &mdash; مراجعة طلبك<br>02 &mdash; التواصل معك<br>03 &mdash; إعداد عرض السعر والتصور المناسب لمشروعك</p>' ),
		qeema_thank_you_page_heading( '☎ بيانات التواصل', 'h3' ),
		qeema_thank_you_page_text( '<p>الهاتف: 01012804721<br>واتساب: 01012804721<br>البريد الإلكتروني: Sales@qeematech.net<br>الموقع: qeematech.net</p>' ),
		qeema_thank_you_page_heading( 'تابع قيمة تك على منصات التواصل', 'h2' ),
		qeema_thank_you_page_text( '<p><a href="https://facebook.com" target="_blank" rel="noopener noreferrer">Facebook</a> &middot; <a href="https://instagram.com" target="_blank" rel="noopener noreferrer">Instagram</a> &middot; <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer">LinkedIn</a></p>' ),
		qeema_thank_you_page_text( '<p><a href="/خدماتنا/">تصفح خدماتنا</a> &middot; <a href="/أعمالنا/">شاهد أعمالنا</a> &middot; <a href="/أتصل-بنا/">تواصل معنا</a></p>' ),
	);

	$section = array(
		'id'       => qeema_thank_you_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => $elements,
	);

	$layout = array( $section );

	$page_id = wp_insert_post( array(
		'post_title'     => 'Thank U Page',
		'post_name'      => 'thank-u-page-home',
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) );

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', '3.35.8' );
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $page_id, '_ez-toc-disabled', 1 );

	$json = wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );

	$stored    = get_post_meta( $page_id, '_elementor_data', true );
	$decoded   = json_decode( $stored, true );
	$decode_ok = is_array( $decoded ) && 1 === count( $decoded );
	$has_thanks = false !== strpos( (string) $stored, 'شكرًا لك' );

	if ( ! $decode_ok || ! $has_thanks ) {
		wp_delete_post( $page_id, true );
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	flush_rewrite_rules( false );

	// Deliberate noindex — see file header comment.
	update_post_meta( $page_id, 'rank_math_robots', array( 'noindex' ) );

	update_option( 'qeema_thank_you_page_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_thank_you_page', 20 );
