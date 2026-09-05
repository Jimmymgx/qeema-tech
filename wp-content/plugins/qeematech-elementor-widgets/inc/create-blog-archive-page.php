<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One-time, idempotent setup: creates the "Blog" page (a plain WP Page, not
 * an Elementor Theme Builder template — matching how every other page on
 * this site is built) at slug 'المدونه', which the site-wide header nav's
 * "المدونة" item already points to (confirmed in the qeema-site-header
 * widget's saved nav_items), so the nav link starts resolving with zero
 * widget edits once this page exists.
 *
 * Mirrors create-single-post-template.php's pattern for creating Elementor
 * content from scratch: a brand-new post with no prior content to protect,
 * so a plain update_post_meta() + wp_slash() write is correct here (unlike
 * the raw-$wpdb+transaction protocol this project uses when rewriting an
 * EXISTING page's live content, where the extra safety net guards against
 * destroying something real).
 */
function qeema_blog_archive_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_blog_archive_page_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => qeema_blog_archive_page_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => $widget_type,
	);
}

function qeema_maybe_create_blog_archive_page() {
	if ( get_option( 'qeema_blog_archive_page_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'page',
		'name'           => 'المدونه',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_blog_archive_page_ready', true, true );
		return;
	}

	$hero_section = array(
		'id'       => qeema_blog_archive_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_blog_archive_page_widget( 'qeema-about-hero', array(
				'eyebrow'        => '',
				'heading'        => 'آخر المقالات',
				'heading_tag'    => 'h1',
				'subheading'     => 'تابع أحدث المقالات والمواضيع التقنية في تصميم وتطوير المواقع، تطبيقات الجوال، التسويق الإلكتروني، والمتاجر الإلكترونية من فريق قيمة تك.',
				'visual_variant' => 'none',
				'buttons'        => array(),
			) ),
		),
	);

	$main_column = array(
		'id'       => qeema_blog_archive_page_qid(),
		'elType'   => 'container',
		'isInner'  => true,
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'qeema-post-main',
		),
		'elements' => array(
			qeema_blog_archive_page_widget( 'qeema-blog-archive', array(
				'posts_per_page' => 12,
				'category'       => '',
				'excerpt_words'  => 24,
			) ),
		),
	);

	$sidebar_column = array(
		'id'       => qeema_blog_archive_page_qid(),
		'elType'   => 'container',
		'isInner'  => true,
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'qeema-post-sidebar',
		),
		'elements' => array(
			qeema_blog_archive_page_widget( 'qeema-blog-sidebar-latest', array(
				'heading' => 'أحدث المقالات',
				'count'   => 5,
			) ),
		),
	);

	$layout_section = array(
		'id'       => qeema_blog_archive_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'qeema-post-layout',
		),
		'elements' => array( $main_column, $sidebar_column ),
	);

	$cta_section = array(
		'id'       => qeema_blog_archive_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_blog_archive_page_widget( 'qeema-cta-banner', array(
				'eyebrow'    => '',
				'heading'    => 'مستعد تبني مشروعك القادم معنا؟',
				'subheading' => 'لنحوّل فكرتك إلى منتج رقمي حقيقي، بخبرة وشغف وتنفيذ يليق بطموحك.',
				'stat_recap' => '',
				'buttons'    => array(
					array(
						'text'  => 'اطلب مشروعك',
						'link'  => array( 'url' => '/أتصل-بنا/' ),
						'style' => 'primary',
					),
				),
			) ),
		),
	);

	$layout = array( $hero_section, $layout_section, $cta_section );

	$page_id = wp_insert_post( array(
		'post_title'     => 'المدونة',
		'post_name'      => 'المدونه',
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
	// Every other page on this site uses this template — it renders the
	// body via Elementor directly rather than through the theme's normal
	// page.php -> the_content() pipeline. Without it (confirmed by direct
	// comparison against the other 5 pages), the page falls back to the
	// default template, which DOES run the_content() — and Easy Table of
	// Contents hooks that same filter and, seeing a cluster of h3 card
	// titles, misfires a spurious "جدول المحتويات" box at the top of the
	// page. Matching the sitewide template avoids that entirely.
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	// Every other page on this site has this set — Easy Table of Contents'
	// auto-insert is JS-driven (scans the rendered DOM for heading tags
	// client-side) and applies to post_type 'page' globally by default;
	// without this per-post override it misfires a "جدول المحتويات" box
	// built from this page's many .qeema-blog-card__title <h3> elements.
	update_post_meta( $page_id, '_ez-toc-disabled', 1 );

	$json = wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );

	// Verify the write actually landed intact before trusting the page —
	// same spirit as this project's raw-$wpdb verification step, just via
	// update_post_meta's own storage path (which correctly unslashes, unlike
	// a raw $wpdb write) since there's no prior content here to protect.
	$stored       = get_post_meta( $page_id, '_elementor_data', true );
	$decoded      = json_decode( $stored, true );
	$decode_ok    = is_array( $decoded ) && 3 === count( $decoded );
	$has_archive  = false !== strpos( (string) $stored, 'qeema-blog-archive' );
	$has_sidebar  = false !== strpos( (string) $stored, 'qeema-post-sidebar' );

	if ( ! $decode_ok || ! $has_archive || ! $has_sidebar ) {
		wp_delete_post( $page_id, true );
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	// Makes '/المدونه/page/2/' resolvable — required once for a newly
	// created page's paginated sub-URLs, easy to forget and a common cause
	// of a silent 404 that looks like a code bug but isn't.
	flush_rewrite_rules( false );

	update_post_meta( $page_id, 'rank_math_description', 'تصفح أحدث المقالات من قيمة تك حول تصميم المواقع، تطبيقات الجوال، التسويق الإلكتروني، والمتاجر الإلكترونية — نصائح عملية من فريقنا لمساعدتك على النمو رقمياً.' );

	update_option( 'qeema_blog_archive_page_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_blog_archive_page', 20 );
