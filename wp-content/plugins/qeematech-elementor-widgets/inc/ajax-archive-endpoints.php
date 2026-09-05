<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX endpoints backing blog-archive-widget.php's and
 * portfolio-archive-widget.php's AJAX pagination/filter swap
 * (assets/js/ajax-archive.js). Deliberately kept OUTSIDE the widget files
 * themselves: those are only require_once'd via qeema_register_widgets(),
 * which runs on the 'elementor/widgets/register' action — an action that
 * does not fire on a plain admin-ajax.php request (confirmed: a wp_ajax_*
 * hook registered inside a widget file silently never ran, and admin-ajax.php
 * fell through to its own "no such action" response). This file is required
 * unconditionally from the main plugin bootstrap instead, so the hooks are
 * always registered; each handler lazily require_once's its own widget file
 * only when an actual matching AJAX request comes in, by which point
 * Elementor's \Elementor\Widget_Base class is guaranteed to already be
 * loaded (WordPress finishes bootstrapping all active plugins, Elementor
 * included, long before any wp_ajax_* callback can run).
 *
 * Registered for both logged-in and logged-out requests since these are
 * public archives with no privileged data — same trust level as the plain
 * GET '?cat='/'/page/N/' requests they're replacing, not a new capability.
 */

function qeema_portfolio_archive_ajax_fetch() {
	require_once __DIR__ . '/../widgets/portfolio-archive-widget.php';

	$page_id        = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
	$page_permalink = $page_id ? get_permalink( $page_id ) : home_url( '/' );
	$paged          = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;
	$current_cat    = ! empty( $_POST['cat'] ) ? sanitize_title( wp_unslash( $_POST['cat'] ) ) : '';
	// Clamped server-side regardless of what the client sends — this is a
	// public unauthenticated endpoint, so the requested page size shouldn't
	// be trusted as-is.
	$posts_per_page  = isset( $_POST['posts_per_page'] ) ? max( 1, min( 48, absint( $_POST['posts_per_page'] ) ) ) : 12;
	$all_label       = ! empty( $_POST['all_label'] ) ? sanitize_text_field( wp_unslash( $_POST['all_label'] ) ) : 'الكل';
	$locked_category = ! empty( $_POST['locked_category'] ) ? sanitize_title( wp_unslash( $_POST['locked_category'] ) ) : '';

	$widget = new \Qeema_Portfolio_Archive_Widget();
	$html   = $widget->render_archive_content( $posts_per_page, $all_label, $paged, $current_cat, $page_permalink, $locked_category );

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_qeema_portfolio_archive_fetch', 'qeema_portfolio_archive_ajax_fetch' );
add_action( 'wp_ajax_nopriv_qeema_portfolio_archive_fetch', 'qeema_portfolio_archive_ajax_fetch' );

function qeema_blog_archive_ajax_fetch() {
	require_once __DIR__ . '/../widgets/blog-archive-widget.php';

	$page_id        = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
	$page_permalink = $page_id ? get_permalink( $page_id ) : home_url( '/' );
	$paged          = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;
	$category       = ! empty( $_POST['category'] ) ? sanitize_title( wp_unslash( $_POST['category'] ) ) : '';
	// Clamped server-side regardless of what the client sends — this is a
	// public unauthenticated endpoint, so the requested page size/excerpt
	// length shouldn't be trusted as-is.
	$posts_per_page = isset( $_POST['posts_per_page'] ) ? max( 1, min( 48, absint( $_POST['posts_per_page'] ) ) ) : 12;
	$excerpt_words  = isset( $_POST['excerpt_words'] ) ? max( 1, min( 100, absint( $_POST['excerpt_words'] ) ) ) : 24;

	$widget = new \Qeema_Blog_Archive_Widget();
	$html   = $widget->render_archive_content( $posts_per_page, $category, $excerpt_words, $paged, $page_permalink );

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_qeema_blog_archive_fetch', 'qeema_blog_archive_ajax_fetch' );
add_action( 'wp_ajax_nopriv_qeema_blog_archive_fetch', 'qeema_blog_archive_ajax_fetch' );
