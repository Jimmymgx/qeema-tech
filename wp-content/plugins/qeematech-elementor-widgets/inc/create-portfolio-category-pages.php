<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One-time, idempotent setup: creates the 6 أعمالنا category landing pages
 * that already have working nav-dropdown links pointing at them (confirmed
 * in the qeema-site-header widget's saved nav_items — same "nav item exists,
 * page doesn't" pattern every other page-creation script on this project has
 * fixed). Each page is a real standalone page on the old live site (own hero
 * + a portfolio grid) — NOT a filter view of the main أعمالنا archive.
 *
 * Old-site correction: the old site's grids on these 6 pages are NOT
 * actually filtered by category (confirmed live: all 6 render the identical
 * ~155-item full catalog). The rebuilt versions here deliberately DO filter
 * for real, using portfolio-archive-widget.php's new `locked_category`
 * control — a considered improvement over the old site's apparent bug, per
 * explicit user decision.
 *
 * The 7th نav-dropdown link, live-app, is NOT a portfolio-category page at
 * all (it's an app-store showcase) and is built by its own separate script.
 *
 * Mirrors create-works-archive-page.php's exact pattern: brand-new pages, so
 * plain update_post_meta()+wp_slash() is correct (not the raw-$wpdb
 * protocol, which is for editing already-live content).
 */
function qeema_cat_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_cat_page_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => qeema_cat_page_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => $widget_type,
	);
}

function qeema_maybe_create_portfolio_category_pages() {
	if ( get_option( 'qeema_portfolio_category_pages_ready' ) ) {
		return;
	}

	// Page slug => [ taxonomy term slug to lock to, page title, hero settings ].
	// Verbatim content fetched directly from the real old-site pages. Term
	// slugs confirmed against the actual stored portfolio-categories terms —
	// note متاجر-إلكترونية (page slug) vs متاجر-الالكترونية (term slug) is a
	// real, deliberate mismatch in the old site's own naming, not a typo here.
	$pages = array(
		'مواقع-الشركات'   => array(
			'term'  => 'مواقع-الشركات',
			'title' => 'مواقع الشركات',
			'hero'  => array(
				'badge'          => 'مواقع الشركات',
				'heading'        => 'موقع شركة يعكس قوتك ويعزز حضورك الرقمي',
				'description'    => 'نصمم مواقع شركات احترافية تعبر عن هويتك وتعرض خدماتك بشكل واضح.',
				'visual_variant' => 'browser',
				'icon'           => '',
				'cta_text'       => 'ابدأ موقعك الآن',
			),
		),
		'تطبيقات-الهاتف'  => array(
			'term'  => 'تطبيقات-الهاتف',
			'title' => 'تطبيقات الهاتف',
			'hero'  => array(
				'badge'          => 'تطبيقات الموبايل',
				'heading'        => 'نبني تطبيقات سلسة وذكية تواكب نمو أعمالك',
				'description'    => 'نطوّر تطبيقات Android و iOS بواجهات حديثة وتجربة استخدام قوية، تساعدك على الوصول إلى عملائك بشكل أسرع وأكثر احترافية.',
				'visual_variant' => 'phone',
				'icon'           => '',
				'cta_text'       => 'ابدأ تطبيقك الآن',
			),
		),
		'متاجر-إلكترونية' => array(
			'term'  => 'متاجر-الالكترونية',
			'title' => 'متاجر إلكترونية',
			'hero'  => array(
				'badge'          => 'المتاجر الإلكترونية',
				'heading'        => 'نبني متجرًا إلكترونيًا جاهزًا للبيع والنمو',
				'description'    => 'نصمم متاجر احترافية تساعدك على عرض منتجاتك بشكل جذاب، وتسهّل رحلة الشراء والدفع، لتزيد الطلبات والمبيعات بشكل مستمر.',
				'visual_variant' => 'icon',
				'icon'           => 'fas fa-store',
				'cta_text'       => 'ابدأ متجرك الآن',
			),
		),
		'مواقع-تعليمية'   => array(
			'term'  => 'مواقع-تعليمية',
			'title' => 'مواقع تعليمية',
			'hero'  => array(
				'badge'          => 'مواقع تعليمية',
				'heading'        => 'منصات تعليمية تجذب المتعلمين وتنظم المحتوى باحتراف',
				'description'    => 'نصمم مواقع ومنصات تعليمية متكاملة لعرض الدورات والبرامج التعليمية، مع تجربة استخدام سهلة تساعد الطلاب على التعلم والمتابعة بشكل أفضل.',
				'visual_variant' => 'icon',
				'icon'           => 'fas fa-graduation-cap',
				'cta_text'       => 'ابدأ منصتك الآن',
			),
		),
		'مواقع-سياحية'    => array(
			'term'  => 'مواقع-سياحية',
			'title' => 'مواقع سياحية',
			'hero'  => array(
				'badge'          => 'مواقع سياحية',
				'heading'        => 'تجربة سياحية تلهم الزائر وتزيد الحجوزات',
				'description'    => 'نصمم مواقع سياحية جذابة تساعدك على عرض الرحلات والخدمات والفنادق والبرامج بشكل احترافي، مع تجربة واضحة تشجع الزائر على الحجز والاستكشاف.',
				'visual_variant' => 'icon',
				'icon'           => 'fas fa-plane',
				'cta_text'       => 'ابدأ مشروعك الآن',
			),
		),
		'برمجة-خاصة'      => array(
			'term'  => 'برمجة-خاصة',
			'title' => 'برمجة خاصة',
			'hero'  => array(
				'badge'          => 'Custom Development',
				'heading'        => 'نبني لك نظام برمجي على مقاس شغلك',
				'description'    => 'حلول برمجية مخصصة 100% مصممة حسب احتياجاتك، تساعدك على الأتمتة، زيادة الكفاءة، وربط كل أنظمة عملك في مكان واحد.',
				'visual_variant' => 'icon',
				'icon'           => 'fas fa-code',
				'cta_text'       => 'ابدأ مشروعك',
			),
		),
	);

	foreach ( $pages as $slug => $config ) {
		$existing = get_posts( array(
			'post_type'      => 'page',
			'name'           => $slug,
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		if ( ! empty( $existing ) ) {
			continue;
		}

		$hero_slide = array(
			'badge'              => $config['hero']['badge'],
			'heading'            => $config['hero']['heading'],
			'description'        => $config['hero']['description'],
			'visual_variant'     => $config['hero']['visual_variant'],
			'icon'               => $config['hero']['icon'],
			'cta_text'           => $config['hero']['cta_text'],
			'cta_link'           => array( 'url' => '/أتصل-بنا/' ),
		);

		$hero_section = array(
			'id'       => qeema_cat_page_qid(),
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => array( 'content_width' => 'full' ),
			'elements' => array(
				qeema_cat_page_widget( 'qeema-works-hero-slider', array(
					'slides'             => array( $hero_slide ),
					'secondary_cta_text' => 'شاهد أعمالنا',
				) ),
			),
		);

		$grid_section = array(
			'id'       => qeema_cat_page_qid(),
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => array(
				'content_width' => 'full',
				'css_classes'   => 'qeema-portfolio-main',
			),
			'elements' => array(
				qeema_cat_page_widget( 'qeema-portfolio-archive', array(
					'posts_per_page'  => 12,
					'locked_category' => $config['term'],
				) ),
			),
		);

		$cta_section = array(
			'id'       => qeema_cat_page_qid(),
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => array( 'content_width' => 'full' ),
			'elements' => array(
				qeema_cat_page_widget( 'qeema-cta-banner', array(
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

		$layout = array( $hero_section, $grid_section, $cta_section );

		$page_id = wp_insert_post( array(
			'post_title'     => $config['title'],
			'post_name'      => $slug,
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		) );

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			continue;
		}

		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $page_id, '_elementor_version', '3.35.8' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
		update_post_meta( $page_id, '_ez-toc-disabled', 1 );

		$json = wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );

		$stored      = get_post_meta( $page_id, '_elementor_data', true );
		$decoded     = json_decode( $stored, true );
		$decode_ok   = is_array( $decoded ) && 3 === count( $decoded );
		$has_hero    = false !== strpos( (string) $stored, 'qeema-works-hero-slider' );
		$has_archive = false !== strpos( (string) $stored, 'qeema-portfolio-archive' );

		if ( ! $decode_ok || ! $has_hero || ! $has_archive ) {
			wp_delete_post( $page_id, true );
			continue;
		}

		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		update_post_meta( $page_id, 'rank_math_description', $config['hero']['description'] );
	}

	flush_rewrite_rules( false );
	update_option( 'qeema_portfolio_category_pages_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_portfolio_category_pages', 20 );
