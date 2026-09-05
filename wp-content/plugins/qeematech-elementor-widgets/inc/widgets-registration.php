<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget category grouping: one category per page the widget was pulled
 * from, plus a shared category for anything reused across more than one
 * page (per the "one widget per unique design, reused" decision — a widget
 * used on both the homepage and the ODOO page can't honestly live under
 * just one page's category).
 */
function qeema_register_widget_categories( $elements_manager ) {
	$elements_manager->add_category( 'qeema-shared-sections', array(
		'title' => __( 'Qeematech - Shared Sections', 'qeematech-elementor-widgets' ),
		'icon'  => 'fa fa-plug',
	) );
}
add_action( 'elementor/elements/categories_registered', 'qeema_register_widget_categories' );

/**
 * Only scripts are registered here — all CSS for these widgets now lives in
 * the qeematech-custom-css plugin's single consolidated stylesheet, enqueued
 * at priority 999 so it reliably wins the cascade against Elementor's own
 * global/kit CSS.
 */
function qeema_register_widget_assets() {
	$plugin_url  = plugin_dir_url( __DIR__ );
	$plugin_path = plugin_dir_path( __DIR__ );

	// filemtime() per-script cache-busts so JS edits show up on next load
	// instead of needing a hard-refresh past a stale browser-cached copy.
	$script_version = function ( $rel_path ) use ( $plugin_path ) {
		$file = $plugin_path . $rel_path;
		return file_exists( $file ) ? filemtime( $file ) : '0.1.0';
	};

	wp_register_script( 'qeema-hero-section', $plugin_url . 'assets/js/hero-section.js', array( 'jquery' ), $script_version( 'assets/js/hero-section.js' ), true );
	wp_register_script( 'qeema-stats-counter', $plugin_url . 'assets/js/stats-counter.js', array( 'jquery' ), $script_version( 'assets/js/stats-counter.js' ), true );
	wp_register_script( 'qeema-site-header', $plugin_url . 'assets/js/site-header.js', array( 'jquery' ), $script_version( 'assets/js/site-header.js' ), true );
	wp_register_script( 'qeema-testimonials-carousel', $plugin_url . 'assets/js/testimonials-carousel.js', array( 'jquery' ), $script_version( 'assets/js/testimonials-carousel.js' ), true );
	wp_register_script( 'qeema-portfolio-grid', $plugin_url . 'assets/js/portfolio-grid.js', array( 'jquery' ), $script_version( 'assets/js/portfolio-grid.js' ), true );
	wp_register_script( 'qeema-site-footer', $plugin_url . 'assets/js/site-footer.js', array(), $script_version( 'assets/js/site-footer.js' ), true );
	wp_register_script( 'qeema-blog-carousel', $plugin_url . 'assets/js/blog-carousel.js', array( 'jquery' ), $script_version( 'assets/js/blog-carousel.js' ), true );
	wp_register_script( 'qeema-scroll-reveal', $plugin_url . 'assets/js/scroll-reveal.js', array( 'jquery' ), $script_version( 'assets/js/scroll-reveal.js' ), true );
	wp_register_script( 'qeema-faq-accordion', $plugin_url . 'assets/js/faq-accordion.js', array(), $script_version( 'assets/js/faq-accordion.js' ), true );
	wp_register_script( 'qeema-works-hero-slider', $plugin_url . 'assets/js/works-hero-slider.js', array( 'jquery' ), $script_version( 'assets/js/works-hero-slider.js' ), true );
	wp_register_script( 'qeema-ajax-archive', $plugin_url . 'assets/js/ajax-archive.js', array(), $script_version( 'assets/js/ajax-archive.js' ), true );
	wp_register_script( 'qeema-bank-details', $plugin_url . 'assets/js/bank-details.js', array(), $script_version( 'assets/js/bank-details.js' ), true );
	wp_register_script( 'qeema-app-logo-accent', $plugin_url . 'assets/js/app-logo-accent.js', array(), $script_version( 'assets/js/app-logo-accent.js' ), true );
	wp_register_script( 'qeema-live-apps-carousel', $plugin_url . 'assets/js/live-apps-carousel.js', array(), $script_version( 'assets/js/live-apps-carousel.js' ), true );

}
add_action( 'wp_enqueue_scripts', 'qeema_register_widget_assets' );
add_action( 'elementor/editor/before_enqueue_scripts', 'qeema_register_widget_assets' );

/**
 * 'font-awesome-5-all' is only registered by Elementor's legacy v4-icon shim
 * path, not unconditionally - referencing it as a wp_enqueue_style dependency
 * was a no-op and silently left every fa/fab icon in these widgets unstyled.
 * Elementor's own bundled Font Awesome 5 file is loaded directly instead, but
 * as a non-blocking preload+swap (same-origin, but it's a large icon-font
 * stylesheet loaded unconditionally on every page regardless of whether that
 * page uses any icons) rather than a normal render-blocking wp_enqueue_style.
 */
function qeema_preload_font_awesome() {
	$fa_url = ELEMENTOR_ASSETS_URL . 'lib/font-awesome/css/all.min.css';
	?>
	<link rel="preload" as="style" href="<?php echo esc_url( $fa_url ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( $fa_url ); ?>" media="print" onload="this.media='all'">
	<noscript><link rel="stylesheet" href="<?php echo esc_url( $fa_url ); ?>"></noscript>
	<?php
}
add_action( 'wp_head', 'qeema_preload_font_awesome', 1 );

function qeema_register_widgets( $widgets_manager ) {
	require_once __DIR__ . '/../widgets/hero-section-widget.php';
	require_once __DIR__ . '/../widgets/stats-counter-widget.php';
	require_once __DIR__ . '/../widgets/feature-grid-widget.php';
	require_once __DIR__ . '/../widgets/site-header-widget.php';
	require_once __DIR__ . '/../widgets/site-footer-widget.php';
	require_once __DIR__ . '/../widgets/portfolio-teaser-widget.php';
	require_once __DIR__ . '/../widgets/testimonials-carousel-widget.php';
	require_once __DIR__ . '/../widgets/blog-grid-widget.php';
	require_once __DIR__ . '/../widgets/why-us-steps-widget.php';
	require_once __DIR__ . '/../widgets/about-hero-widget.php';
	require_once __DIR__ . '/../widgets/pull-quote-widget.php';
	require_once __DIR__ . '/../widgets/value-cards-widget.php';
	require_once __DIR__ . '/../widgets/trusted-by-logos-widget.php';
	require_once __DIR__ . '/../widgets/cta-banner-widget.php';
	require_once __DIR__ . '/../widgets/related-posts-widget.php';
	require_once __DIR__ . '/../widgets/blog-archive-widget.php';
	require_once __DIR__ . '/../widgets/blog-sidebar-latest-widget.php';
	require_once __DIR__ . '/../widgets/share-buttons-widget.php';
	require_once __DIR__ . '/../widgets/sidebar-cta-widget.php';
	require_once __DIR__ . '/../widgets/post-categories-widget.php';
	require_once __DIR__ . '/../widgets/faq-widget.php';
	require_once __DIR__ . '/../widgets/browser-showcase-widget.php';
	require_once __DIR__ . '/../widgets/tech-stack-widget.php';
	require_once __DIR__ . '/../widgets/app-store-proof-widget.php';
	require_once __DIR__ . '/../widgets/category-showcase-widget.php';
	require_once __DIR__ . '/../widgets/works-hero-slider-widget.php';
	require_once __DIR__ . '/../widgets/portfolio-archive-widget.php';
	require_once __DIR__ . '/../widgets/thank-you-success-hero-widget.php';
	require_once __DIR__ . '/../widgets/bank-details-widget.php';
	require_once __DIR__ . '/../widgets/portfolio-case-hero-widget.php';
	require_once __DIR__ . '/../widgets/portfolio-case-story-widget.php';
	require_once __DIR__ . '/../widgets/live-apps-carousel-widget.php';
	require_once __DIR__ . '/../widgets/live-apps-grid-widget.php';

	$widgets_manager->register( new \Qeema_Hero_Section_Widget() );
	$widgets_manager->register( new \Qeema_Stats_Counter_Widget() );
	$widgets_manager->register( new \Qeema_Feature_Grid_Widget() );
	$widgets_manager->register( new \Qeema_Site_Header_Widget() );
	$widgets_manager->register( new \Qeema_Site_Footer_Widget() );
	$widgets_manager->register( new \Qeema_Portfolio_Teaser_Widget() );
	$widgets_manager->register( new \Qeema_Testimonials_Carousel_Widget() );
	$widgets_manager->register( new \Qeema_Blog_Grid_Widget() );
	$widgets_manager->register( new \Qeema_Why_Us_Steps_Widget() );
	$widgets_manager->register( new \Qeema_About_Hero_Widget() );
	$widgets_manager->register( new \Qeema_Pull_Quote_Widget() );
	$widgets_manager->register( new \Qeema_Value_Cards_Widget() );
	$widgets_manager->register( new \Qeema_Trusted_By_Widget() );
	$widgets_manager->register( new \Qeema_Cta_Banner_Widget() );
	$widgets_manager->register( new \Qeema_Related_Posts_Widget() );
	$widgets_manager->register( new \Qeema_Blog_Archive_Widget() );
	$widgets_manager->register( new \Qeema_Blog_Sidebar_Latest_Widget() );
	$widgets_manager->register( new \Qeema_Share_Buttons_Widget() );
	$widgets_manager->register( new \Qeema_Sidebar_Cta_Widget() );
	$widgets_manager->register( new \Qeema_Post_Categories_Widget() );
	$widgets_manager->register( new \Qeema_Faq_Widget() );
	$widgets_manager->register( new \Qeema_Browser_Showcase_Widget() );
	$widgets_manager->register( new \Qeema_Tech_Stack_Widget() );
	$widgets_manager->register( new \Qeema_App_Store_Proof_Widget() );
	$widgets_manager->register( new \Qeema_Category_Showcase_Widget() );
	$widgets_manager->register( new \Qeema_Works_Hero_Slider_Widget() );
	$widgets_manager->register( new \Qeema_Portfolio_Archive_Widget() );
	$widgets_manager->register( new \Qeema_Thank_You_Success_Hero_Widget() );
	$widgets_manager->register( new \Qeema_Bank_Details_Widget() );
	$widgets_manager->register( new \Qeema_Portfolio_Case_Hero_Widget() );
	$widgets_manager->register( new \Qeema_Portfolio_Case_Story_Widget() );
	$widgets_manager->register( new \Qeema_Live_Apps_Carousel_Widget() );
	$widgets_manager->register( new \Qeema_Live_Apps_Grid_Widget() );
}
add_action( 'elementor/widgets/register', 'qeema_register_widgets' );
