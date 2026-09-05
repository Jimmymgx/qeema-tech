<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the مواقع شركات المقاولات (Contracting Companies Websites) niche
 * landing page — a real fleshed-out page on the old site (features/process/
 * CTA/FAQ, confirmed to have no portfolio grid and no testimonials despite
 * raw-HTML false positives from unused global CSS/JS class names). Mirrors
 * this project's already-established service-page assembly pattern —
 * qeema-about-hero, qeema-feature-grid (reused for both the feature list and
 * the site-pages list, matching the "icon cards" shape both old-site
 * sections actually use), qeema-cta-banner, qeema-faq — no new widgets.
 */
function qeema_contracting_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_contracting_page_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => qeema_contracting_page_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => $widget_type,
	);
}

function qeema_contracting_page_section( $widget ) {
	return array(
		'id'       => qeema_contracting_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array( $widget ),
	);
}

function qeema_maybe_create_contracting_landing_page() {
	if ( get_option( 'qeema_contracting_landing_page_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'page',
		'name'           => 'مواقع-شركات-المقاولات',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_contracting_landing_page_ready', true, true );
		return;
	}

	$hero = qeema_contracting_page_section( qeema_contracting_page_widget( 'qeema-about-hero', array(
		'eyebrow'        => '',
		'heading'        => 'موقع إلكتروني احترافي يعرض قوة شركة المقاولات الخاصة بك',
		'heading_tag'    => 'h1',
		'subheading'     => 'نصمم مواقع إلكترونية حديثة لشركات المقاولات تعرض مشاريعك وخدماتك باحترافية وتساعدك على كسب ثقة عملائك.',
		'visual_variant' => 'none',
		'buttons'        => array(),
	) ) );

	$features = qeema_contracting_page_section( qeema_contracting_page_widget( 'qeema-feature-grid', array(
		'badge'      => '',
		'heading'    => 'ماذا يشمل تصميم موقع شركة مقاولات؟',
		'subheading' => '',
		'columns'    => '3',
		'card_style' => 'icons',
		'cards'      => array(
			array( 'icon_text' => 'fas fa-landmark', 'title' => 'بناء الحاضر بثقة', 'description' => '' ),
			array( 'icon_text' => 'fas fa-drafting-compass', 'title' => 'تصميم احترافي', 'description' => '' ),
			array( 'icon_text' => 'fas fa-building', 'title' => 'عرض المشاريع', 'description' => '' ),
			array( 'icon_text' => 'fas fa-file-invoice', 'title' => 'طلبات عروض سعر', 'description' => '' ),
			array( 'icon_text' => 'fas fa-search', 'title' => 'تهيئة لمحركات البحث', 'description' => '' ),
		),
		'buttons'    => array(),
	) ) );

	$project_types = qeema_contracting_page_section( qeema_contracting_page_widget( 'qeema-feature-grid', array(
		'badge'      => '',
		'heading'    => 'نصمم مواقع تناسب مختلف شركات المقاولات',
		'subheading' => 'سواء كانت شركتك تعمل في المشاريع الإنشائية، التشطيبات، البنية التحتية، أو الصيانة — نبني لك موقعًا يعكس طبيعة نشاطك.',
		'columns'    => '4',
		'card_style' => 'icons',
		'cards'      => array(
			array( 'icon_text' => 'fas fa-hard-hat', 'title' => 'إنشائية', 'description' => '' ),
			array( 'icon_text' => 'fas fa-paint-roller', 'title' => 'تشطيبات', 'description' => '' ),
			array( 'icon_text' => 'fas fa-road', 'title' => 'بنية تحتية', 'description' => '' ),
			array( 'icon_text' => 'fas fa-tools', 'title' => 'صيانة', 'description' => '' ),
		),
		'buttons'    => array(),
	) ) );

	$site_pages = qeema_contracting_page_section( qeema_contracting_page_widget( 'qeema-feature-grid', array(
		'badge'      => '',
		'heading'    => 'موقع مصمم ليخدم أهداف شركة المقاولات',
		'subheading' => '',
		'columns'    => '3',
		'card_style' => 'icons',
		'cards'      => array(
			array( 'icon_text' => 'fas fa-info-circle', 'title' => 'صفحة تعريف بالشركة', 'description' => '' ),
			array( 'icon_text' => 'fas fa-cogs', 'title' => 'صفحة الخدمات', 'description' => '' ),
			array( 'icon_text' => 'fas fa-images', 'title' => 'معرض المشاريع', 'description' => '' ),
			array( 'icon_text' => 'fas fa-folder-open', 'title' => 'صفحة لكل مشروع', 'description' => '' ),
			array( 'icon_text' => 'fas fa-file-signature', 'title' => 'طلب عرض سعر', 'description' => '' ),
			array( 'icon_text' => 'fas fa-search-dollar', 'title' => 'تهيئة SEO', 'description' => '' ),
		),
		'buttons'    => array(),
	) ) );

	$process = qeema_contracting_page_section( qeema_contracting_page_widget( 'qeema-feature-grid', array(
		'badge'      => '',
		'heading'    => 'كيف نبدأ تصميم موقع شركة المقاولات؟',
		'subheading' => '',
		'columns'    => '3',
		'card_style' => 'icons',
		'cards'      => array(
			array( 'icon_text' => '01', 'title' => 'فهم نشاط الشركة', 'description' => '' ),
			array( 'icon_text' => '02', 'title' => 'تخطيط هيكل الموقع', 'description' => '' ),
			array( 'icon_text' => '03', 'title' => 'تصميم الواجهة', 'description' => '' ),
			array( 'icon_text' => '04', 'title' => 'التطوير والربط', 'description' => '' ),
			array( 'icon_text' => '05', 'title' => 'الإطلاق والدعم', 'description' => '' ),
		),
		'buttons'    => array(),
	) ) );

	$cta = qeema_contracting_page_section( qeema_contracting_page_widget( 'qeema-cta-banner', array(
		'eyebrow'    => '',
		'heading'    => 'جاهز تبني حضور رقمي يليق بشركة المقاولات الخاصة بك؟',
		'subheading' => 'لنصمم لك موقعًا يعكس قوة شركتك ويساعدك على كسب المزيد من العملاء والمشاريع.',
		'stat_recap' => '',
		'buttons'    => array(
			array( 'text' => 'اطلب تصميم موقعك الآن', 'link' => array( 'url' => '/أتصل-بنا/' ), 'style' => 'primary' ),
		),
	) ) );

	$faq = qeema_contracting_page_section( qeema_contracting_page_widget( 'qeema-faq', array(
		'badge'      => 'الأسئلة الشائعة',
		'heading'    => 'أسئلة مهمة حول تصميم مواقع شركات المقاولات',
		'subheading' => '',
		'items'      => array(
			array( 'question' => 'ما أهمية تصميم موقع لشركة مقاولات؟', 'answer' => 'الموقع الإلكتروني يمنح شركتك حضورًا احترافيًا يعرض مشاريعك وخدماتك ويكسبك ثقة العملاء الجدد.' ),
			array( 'question' => 'كم تكلفة تصميم موقع شركة مقاولات؟', 'answer' => 'تختلف التكلفة حسب حجم الموقع والمزايا المطلوبة، تواصل معنا للحصول على عرض سعر مناسب لاحتياجاتك.' ),
			array( 'question' => 'هل يمكن عرض مشاريعي على الموقع؟', 'answer' => 'نعم، نصمم صفحة معرض مشاريع تفصيلية لكل مشروع نفذته شركتك.' ),
			array( 'question' => 'هل الموقع متوافق مع الهواتف؟', 'answer' => 'جميع المواقع التي نصممها متجاوبة بالكامل مع جميع أحجام الشاشات.' ),
			array( 'question' => 'هل يشمل الموقع تهيئة لمحركات البحث؟', 'answer' => 'نعم، نراعي أساسيات تهيئة SEO في بنية الموقع من البداية.' ),
		),
		'faq_schema' => 'yes',
	) ) );

	$layout = array( $hero, $features, $project_types, $site_pages, $process, $cta, $faq );

	$page_id = wp_insert_post( array(
		'post_title'     => 'مواقع شركات المقاولات',
		'post_name'      => 'مواقع-شركات-المقاولات',
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
	$decode_ok = is_array( $decoded ) && 7 === count( $decoded );
	$has_faq   = false !== strpos( (string) $stored, 'qeema-faq' );

	if ( ! $decode_ok || ! $has_faq ) {
		wp_delete_post( $page_id, true );
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	flush_rewrite_rules( false );

	update_post_meta( $page_id, 'rank_math_description', 'تصميم مواقع احترافية لشركات المقاولات — عرض المشاريع، طلب عروض الأسعار، وتهيئة لمحركات البحث. اطلب موقعك الآن من قيمة تك.' );

	update_option( 'qeema_contracting_landing_page_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_contracting_landing_page', 20 );
