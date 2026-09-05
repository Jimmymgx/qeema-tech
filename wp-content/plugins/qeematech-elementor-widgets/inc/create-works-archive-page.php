<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One-time, idempotent setup: creates the "أعمالنا" page (a plain WP Page,
 * matching how every other page on this site is built) at slug 'أعمالنا',
 * which the site-wide header nav's top-level "أعمالنا" item already points
 * to (confirmed in the qeema-site-header widget's saved nav_items — the same
 * "nav item exists, page doesn't" pattern the Blog page fixed), so the nav
 * link starts resolving with zero widget edits once this page exists.
 *
 * Mirrors create-blog-archive-page.php's pattern for creating Elementor
 * content from scratch: a brand-new post with no prior content to protect,
 * so a plain update_post_meta() + wp_slash() write is correct here (unlike
 * the raw-$wpdb+transaction protocol this project uses when rewriting an
 * EXISTING page's live content).
 */
function qeema_works_archive_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_works_archive_page_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => qeema_works_archive_page_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => $widget_type,
	);
}

function qeema_maybe_create_works_archive_page() {
	if ( get_option( 'qeema_works_archive_page_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'page',
		'name'           => 'أعمالنا',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_works_archive_page_ready', true, true );
		return;
	}

	// 5 slides matching the old site's real content (badge/heading/description
	// fetched verbatim from qeematech.net/أعمالنا/), one per real portfolio
	// category. Visuals cycle through the 3 reused decorative recipes rather
	// than needing 5 unique illustrations.
	$hero_slides = array(
		array(
			'badge'          => 'مواقع الشركات',
			'heading'        => 'موقع شركة يعكس قوتك ويعزز حضورك الرقمي',
			'description'    => 'نصمم مواقع شركات احترافية تعبر عن هويتك وتعرض خدماتك بشكل واضح.',
			'visual_variant' => 'browser',
			'icon'           => '',
			'cta_text'       => 'ابدأ موقعك الآن',
			'cta_link'       => array( 'url' => '/أتصل-بنا/' ),
		),
		array(
			'badge'          => 'تطبيقات الموبايل',
			'heading'        => 'تطبيقات سلسة وذكية',
			'description'    => 'نطور تطبيقات Android و iOS بواجهات حديثة وتجربة استخدام قوية تليق بمشروعك.',
			'visual_variant' => 'phone',
			'icon'           => '',
			'cta_text'       => 'ابدأ تطبيقك الآن',
			'cta_link'       => array( 'url' => '/أتصل-بنا/' ),
		),
		array(
			'badge'          => 'المتاجر الإلكترونية',
			'heading'        => 'متجر إلكتروني جاهز للبيع',
			'description'    => 'نصمم متاجر إلكترونية سريعة وواضحة تساعدك على البيع أونلاين براحة تامة.',
			'visual_variant' => 'icon',
			'icon'           => 'fas fa-store',
			'cta_text'       => 'ابدأ متجرك الآن',
			'cta_link'       => array( 'url' => '/أتصل-بنا/' ),
		),
		array(
			'badge'          => 'مواقع تعليمية',
			'heading'        => 'منصات تعليمية تنظم المحتوى وتجذب المتعلمين',
			'description'    => 'نبني منصات تعليمية مرنة تنظم المحتوى وتحسّن تجربة المتعلمين.',
			'visual_variant' => 'icon',
			'icon'           => 'fas fa-graduation-cap',
			'cta_text'       => 'ابدأ منصتك الآن',
			'cta_link'       => array( 'url' => '/أتصل-بنا/' ),
		),
		array(
			'badge'          => '',
			'heading'        => 'حلول برمجية مخصصة',
			'description'    => 'نطوّر أنظمة وبرمجيات خاصة مصممة لتلائم طبيعة عملك بدقة.',
			'visual_variant' => 'icon',
			'icon'           => 'fas fa-code',
			'cta_text'       => 'ابدأ مشروعك الآن',
			'cta_link'       => array( 'url' => '/أتصل-بنا/' ),
		),
	);

	$hero_section = array(
		'id'       => qeema_works_archive_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_works_archive_page_widget( 'qeema-works-hero-slider', array(
				'slides'              => $hero_slides,
				'secondary_cta_text'  => 'شاهد أعمالنا',
			) ),
		),
	);

	$heading_section = array(
		'id'       => qeema_works_archive_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_works_archive_page_widget( 'qeema-about-hero', array(
				'eyebrow'        => '',
				'heading'        => 'أخر الأعمال',
				'heading_tag'    => 'h1',
				'subheading'     => 'تقدم شركة قيمة تك أفضل الحلول والعروض المتاحة لـ تصميم المواقع وبرمجة التطبيقات',
				'visual_variant' => 'none',
				'buttons'        => array(),
			) ),
		),
	);

	$grid_section = array(
		'id'       => qeema_works_archive_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'qeema-portfolio-main',
			'_element_id'   => 'portfolio',
		),
		'elements' => array(
			qeema_works_archive_page_widget( 'qeema-portfolio-archive', array(
				'posts_per_page' => 12,
				'all_label'      => 'الكل',
			) ),
		),
	);

	$cta_section = array(
		'id'       => qeema_works_archive_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_works_archive_page_widget( 'qeema-cta-banner', array(
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

	$layout = array( $hero_section, $heading_section, $grid_section, $cta_section );

	$page_id = wp_insert_post( array(
		'post_title'     => 'أعمالنا',
		'post_name'      => 'أعمالنا',
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
	// Every other page on this site uses this template — renders the body via
	// Elementor directly instead of the theme's page.php -> the_content()
	// pipeline. See create-blog-archive-page.php for the ToC-misfire bug this
	// avoids.
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	// Applied from the start this time (the Blog page discovered this fix by
	// trial and error after shipping without it) — Easy Table of Contents'
	// auto-insert is JS-driven and applies to post_type 'page' globally by
	// default; without this per-post override it would misfire a "جدول
	// المحتويات" box built from this page's h2/h3 slide and card titles.
	update_post_meta( $page_id, '_ez-toc-disabled', 1 );

	$json = wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );

	// Verify the write actually landed intact before trusting the page — same
	// spirit as this project's raw-$wpdb verification step, just via
	// update_post_meta's own storage path (which correctly unslashes, unlike
	// a raw $wpdb write) since there's no prior content here to protect.
	$stored      = get_post_meta( $page_id, '_elementor_data', true );
	$decoded     = json_decode( $stored, true );
	$decode_ok   = is_array( $decoded ) && 4 === count( $decoded );
	$has_hero    = false !== strpos( (string) $stored, 'qeema-works-hero-slider' );
	$has_archive = false !== strpos( (string) $stored, 'qeema-portfolio-archive' );

	if ( ! $decode_ok || ! $has_hero || ! $has_archive ) {
		wp_delete_post( $page_id, true );
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	// Makes '/أعمالنا/page/2/' resolvable — required once for a newly created
	// page's paginated sub-URLs, easy to forget and a common cause of a
	// silent 404 that looks like a code bug but isn't.
	flush_rewrite_rules( false );

	update_post_meta( $page_id, 'rank_math_description', 'تصفح أعمالنا في تصميم وتطوير المواقع، تطبيقات الجوال، المتاجر الإلكترونية، والمنصات التعليمية والبرمجيات المخصصة — نماذج حقيقية نفذتها قيمة تك لعملائها.' );

	update_option( 'qeema_works_archive_page_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_works_archive_page', 20 );
