<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paginated portfolio archive — the أعمالنا page's main section: taxonomy
 * filter tabs + a real paginated grid. Reuses the exact card markup/CSS
 * already proven by portfolio-teaser-widget.php's render_grid() (masked
 * bg-image card, hover-reveal badge, title pill) but backed by a finite,
 * paginated WP_Query instead of a `posts_per_page:-1` + client-side-filtered
 * one, since this is a true archive rather than a homepage teaser section.
 * Kept as its own widget rather than adding pagination to the teaser, same
 * reasoning already used for qeema-blog-archive vs the blog-grid teaser.
 *
 * Filter tabs + pagination are progressively enhanced into AJAX swaps by
 * ajax-archive.js (shared with blog-archive-widget.php): render() always
 * emits real, fully working '/page/N/'+'?cat=' links first, so the archive
 * works with JS disabled exactly as it did before — the JS only intercepts
 * clicks on those same links afterward. render_archive_content() is the
 * actual re-usable rendering logic, called both by render() (normal page
 * load) and by the wp_ajax_qeema_portfolio_archive_fetch handler below (on a
 * filter/page click), so both paths are guaranteed to produce identical
 * markup.
 */
class Qeema_Portfolio_Archive_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-portfolio-archive';
	}

	public function get_title() {
		return __( 'Portfolio Archive Grid', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-ajax-archive' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Portfolio Archive', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'posts_per_page', array(
			'label'   => __( 'Posts Per Page', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 12,
		) );

		$this->add_control( 'all_label', array(
			'label'   => __( '"All" Tab Label', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'الكل',
		) );

		$this->add_control( 'locked_category', array(
			'label'       => __( 'Lock To Category Slug (optional)', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'description' => __( 'When set, the grid always shows only this category and the filter tabs are hidden entirely — for single-category landing pages (e.g. one of the أعمالنا category pages) rather than the main archive.', 'qeematech-elementor-widgets' ),
		) );

		$this->end_controls_section();
	}

	/**
	 * Same encode/decode dance already used by portfolio-teaser-widget.php's
	 * render_grid(): these Arabic term slugs are stored percent-encoded by
	 * sanitize_title(), so the human-readable ?cat= value in the URL is the
	 * urldecode()'d form, and matching it back against the terms table needs
	 * sanitize_title() applied again.
	 */
	private function get_current_cat_from_request() {
		if ( empty( $_GET['cat'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public read-only filter, not a state change
			return '';
		}
		return sanitize_title( wp_unslash( $_GET['cat'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}

	private function render_filters( $terms, $current_cat, $all_label, $page_permalink ) {
		?>
		<nav class="qeema-portfolio-archive__filters">
			<a class="qeema-portfolio-archive__filter<?php echo '' === $current_cat ? ' is-active' : ''; ?>" href="<?php echo esc_url( $page_permalink ); ?>">
				<?php echo esc_html( $all_label ); ?>
			</a>
			<?php foreach ( $terms as $term ) :
				$is_active = $current_cat === $term->slug;
				$href      = add_query_arg( 'cat', urldecode( $term->slug ), $page_permalink );
				?>
				<a class="qeema-portfolio-archive__filter<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $href ); ?>">
					<?php echo esc_html( $term->name ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * The actual filters+grid+pagination markup, as a re-usable method so the
	 * AJAX handler (which has no Elementor settings context, no Elementor
	 * widget instance from the page render) can produce byte-identical output
	 * to a real page load — it just needs a fresh instance of this class and
	 * these plain values, no Elementor settings API involved.
	 */
	public function render_archive_content( $posts_per_page, $all_label, $paged, $current_cat, $page_permalink, $locked_category = '' ) {
		ob_start();

		// In locked mode this page never reads $_GET['cat'] at all (see
		// render() below) — $current_cat is forced to $locked_category and
		// the tab bar is skipped entirely, for single-category landing pages
		// (the أعمالنا category pages) rather than the main filterable
		// archive.
		$effective_cat = $locked_category ? $locked_category : $current_cat;

		$args = array(
			'post_type'           => 'portfolio',
			'post_status'         => 'publish',
			'posts_per_page'      => $posts_per_page,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
			// Same stable-sort tiebreaker as blog-archive-widget.php — this
			// content was also batch-imported, so several posts likely share
			// the same post_date.
			'orderby'             => array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
		);
		if ( $effective_cat ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'portfolio-categories',
					'field'    => 'slug',
					'terms'    => array( $effective_cat ),
				),
			);
		}

		if ( ! $locked_category ) {
			$terms = get_terms( array(
				'taxonomy'   => 'portfolio-categories',
				'hide_empty' => true,
			) );
			if ( is_wp_error( $terms ) ) {
				$terms = array();
			}
			$this->render_filters( $terms, $current_cat, $all_label, $page_permalink );
		}

		$query = new WP_Query( $args );
		if ( ! $query->have_posts() ) {
			?>
			<p class="qeema-portfolio-archive__empty"><?php esc_html_e( 'لا توجد أعمال في هذا القسم حالياً.', 'qeematech-elementor-widgets' ); ?></p>
			<?php
			return ob_get_clean();
		}
		?>
		<div class="qeema-portfolio-grid">
			<div class="qeema-portfolio-grid__wrap">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<?php
					$post_id  = get_the_ID();
					$image_id = get_post_thumbnail_id( $post_id );
					if ( ! $image_id ) {
						// Migrated portfolio content often only has its image in the
						// ACF `banner` field, not a real featured image — falling
						// back here (instead of excluding via a query meta_query)
						// keeps those posts from silently vanishing off the grid.
						$image_id = (int) get_post_meta( $post_id, 'banner', true );
					}
					if ( ! $image_id ) {
						continue;
					}

					$terms_on_post = get_the_terms( $post_id, 'portfolio-categories' );
					$cats          = array();
					if ( $terms_on_post && ! is_wp_error( $terms_on_post ) ) {
						foreach ( $terms_on_post as $term ) {
							$cats[] = urldecode( $term->slug );
						}
					}
					// Both card types render as the same mockup-frame shape
					// with the same footer/button markup (see
					// render_browser_card()/render_phone_card()) so a website
					// card and an app card always come out the same size with
					// identically styled buttons - only the top chrome and
					// the primary button's link differ.
					$is_app    = in_array( 'تطبيقات-الهاتف', $cats, true );
					$item_cls  = 'qeema-portfolio-grid__item' . ( $is_app ? ' qeema-portfolio-grid__item--app' : '' );
					?>
					<div class="<?php echo esc_attr( $item_cls ); ?>">
						<?php echo $is_app ? $this->render_phone_card( $post_id, $image_id ) : $this->render_browser_card( $post_id, $image_id ); ?>
					</div>
				<?php endwhile; ?>
			</div>
		</div>

		<?php if ( $query->max_num_pages > 1 ) : ?>
			<nav class="qeema-blog-pagination">
				<?php
				echo paginate_links( array( // phpcs:ignore WordPress.Security.EscapeOutput -- paginate_links() output is already escaped
					'base'      => trailingslashit( $page_permalink ) . '%_%',
					'format'    => 'page/%#%/',
					'current'   => $paged,
					'total'     => $query->max_num_pages,
					'prev_text' => '‹',
					'next_text' => '›',
					'type'      => 'plain',
					// Locked pages never put 'cat' in the URL at all — the
					// category is implicit and constant, so there's nothing
					// to preserve across pagination links.
					'add_args'  => ( ! $locked_category && $current_cat ) ? array( 'cat' => urldecode( $current_cat ) ) : array(),
				) );
				?>
			</nav>
		<?php endif; ?>
		<?php
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Browser-window mockup card for a real website project — see the
	 * identical method on portfolio-teaser-widget.php's render_grid() for
	 * the full rationale; kept as a byte-identical copy here for the same
	 * reason the surrounding grid markup itself is already duplicated
	 * between the two widgets (per the class docblock above).
	 */
	private function render_browser_card( $post_id, $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'large' );
		$permalink = get_permalink( $post_id );
		$external  = function_exists( 'get_field' ) ? get_field( 'link', $post_id ) : '';
		$domain    = '';
		if ( $external ) {
			$host   = wp_parse_url( $external, PHP_URL_HOST );
			$domain = $host ? preg_replace( '/^www\./', '', $host ) : '';
		}
		ob_start();
		?>
		<div class="qeema-portfolio-grid__browser-card">
			<div class="qeema-portfolio-grid__browser-bar">
				<span class="qt-dot r"></span><span class="qt-dot y"></span><span class="qt-dot g"></span>
				<?php if ( $domain ) : ?>
					<span class="qeema-portfolio-grid__browser-url"><?php echo esc_html( $domain ); ?></span>
				<?php endif; ?>
			</div>
			<a class="qeema-portfolio-grid__browser-screen" href="<?php echo esc_url( $permalink ); ?>" style="background-image:url('<?php echo esc_url( $image_url ); ?>')" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"></a>
			<div class="qeema-portfolio-grid__browser-footer">
				<h3 class="qeema-portfolio-grid__browser-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<div class="qeema-portfolio-grid__browser-actions">
					<?php if ( $external ) : ?>
						<a class="qeema-portfolio-grid__browser-btn primary" href="<?php echo esc_url( $external ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'زيارة الموقع', 'qeematech-elementor-widgets' ); ?></a>
					<?php endif; ?>
					<a class="qeema-portfolio-grid__browser-btn ghost" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'عرض المشروع', 'qeematech-elementor-widgets' ); ?></a>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Phone mockup card for a real mobile-app project — see the identical
	 * method on portfolio-teaser-widget.php's render_grid() for the full
	 * rationale; kept as a byte-identical copy here for the same reason the
	 * surrounding grid markup itself is already duplicated between the two
	 * widgets (per the class docblock above).
	 */
	private function render_phone_card( $post_id, $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'large' );
		$permalink = get_permalink( $post_id );
		$android   = function_exists( 'get_field' ) ? get_field( 'android', $post_id ) : '';
		$ios       = function_exists( 'get_field' ) ? get_field( 'ios', $post_id ) : '';
		$store_url = $android ? $android : $ios;
		ob_start();
		?>
		<div class="qeema-portfolio-grid__phone-card">
			<div class="qeema-portfolio-grid__browser-bar qeema-portfolio-grid__phone-bar">
				<span class="qeema-portfolio-grid__phone-notch"></span>
			</div>
			<a class="qeema-portfolio-grid__browser-screen" href="<?php echo esc_url( $permalink ); ?>" style="background-image:url('<?php echo esc_url( $image_url ); ?>')" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"></a>
			<div class="qeema-portfolio-grid__browser-footer">
				<h3 class="qeema-portfolio-grid__browser-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<div class="qeema-portfolio-grid__browser-actions">
					<?php if ( $store_url ) : ?>
						<a class="qeema-portfolio-grid__browser-btn primary" href="<?php echo esc_url( $store_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'تحميل التطبيق', 'qeematech-elementor-widgets' ); ?></a>
					<?php endif; ?>
					<a class="qeema-portfolio-grid__browser-btn ghost" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'عرض المشروع', 'qeematech-elementor-widgets' ); ?></a>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Same page-vs-paged fix confirmed on blog-archive-widget.php: a
		// static Page's '/page/N/' pagination sub-URL populates 'paged', not
		// 'page' (that one is reserved for <!--nextpage-->).
		$paged       = max( 1, (int) get_query_var( 'paged' ) ?: (int) get_query_var( 'page' ) );
		$current_cat = $this->get_current_cat_from_request();
		// $page_id is captured here (while the actual page is the current
		// queried object) and handed to the JS as a data attribute, so the
		// AJAX handler below — which runs via admin-ajax.php with no "current
		// page" context at all — can resolve the same permalink via
		// get_permalink( $page_id ) instead of a bare get_permalink() (which
		// would need a real singular-page context it doesn't have).
		$page_id        = get_the_ID();
		$page_permalink = get_permalink( $page_id );

		$posts_per_page  = ! empty( $settings['posts_per_page'] ) ? intval( $settings['posts_per_page'] ) : 12;
		$all_label       = ! empty( $settings['all_label'] ) ? $settings['all_label'] : 'الكل';
		$locked_category = ! empty( $settings['locked_category'] ) ? sanitize_title( $settings['locked_category'] ) : '';
		?>
		<div class="qeema-portfolio-archive__ajax" data-qeema-ajax-archive
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-ajax-action="qeema_portfolio_archive_fetch"
			data-page-id="<?php echo esc_attr( $page_id ); ?>"
			data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>"
			data-all-label="<?php echo esc_attr( $all_label ); ?>"
			data-locked-category="<?php echo esc_attr( $locked_category ); ?>">
			<?php echo $this->render_archive_content( $posts_per_page, $all_label, $paged, $current_cat, $page_permalink, $locked_category ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped internally ?>
		</div>
		<?php
	}
}

// The AJAX endpoint backing this widget's progressive-enhancement swap lives
// in inc/ajax-archive-endpoints.php, not here — that file is required
// unconditionally from the main plugin bootstrap, unlike this one, which is
// only ever loaded via Elementor's 'elementor/widgets/register' action. That
// action does not fire on a plain admin-ajax.php request, so a wp_ajax_*
// hook registered here would silently never run (confirmed: admin-ajax.php
// returned its own "no such action" fallback until this was moved).
