<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paginated blog archive grid — the Blog page's main column. Reuses the
 * blog-grid widget's `.qeema-blog-card` visual language (verified safe: every
 * property on that bare class is a no-op outside a flex/scroll-snap parent)
 * but inside a real CSS Grid with real WordPress pagination, since this is a
 * true archive rather than a "latest posts" teaser carousel. Kept as its own
 * widget rather than adding a "grid mode" to blog-grid-widget.php, matching
 * this project's convention of not risking an already-reused widget.
 *
 * Pagination is progressively enhanced into an AJAX swap by ajax-archive.js
 * (shared with portfolio-archive-widget.php): render() always emits a real,
 * fully working '/page/N/' link first, so the archive works with JS disabled
 * exactly as before — the JS only intercepts clicks on that same link
 * afterward. render_archive_content() is the actual re-usable rendering
 * logic, called both by render() (normal page load) and by the
 * wp_ajax_qeema_blog_archive_fetch handler below (on a page click), so both
 * paths produce identical markup.
 */
class Qeema_Blog_Archive_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-blog-archive';
	}

	public function get_title() {
		return __( 'Blog Archive Grid', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-ajax-archive' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Blog Archive', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'posts_per_page', array(
			'label'   => __( 'Posts Per Page', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 12,
		) );

		$this->add_control( 'category', array(
			'label'       => __( 'Category Slug (optional)', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'description' => __( 'Leave empty to show the latest posts from any category.', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'excerpt_words', array(
			'label'   => __( 'Excerpt Word Count', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 24,
		) );

		$this->end_controls_section();
	}

	/**
	 * Same default_category-exclusion pattern as related-posts-widget.php /
	 * post-categories-widget.php, so a post still only in "Uncategorized"
	 * shows no badge instead of a meaningless one.
	 */
	private function get_badge_name( $post_id ) {
		$default_cat = (int) get_option( 'default_category' );
		$cats        = wp_get_post_categories( $post_id, array( 'fields' => 'ids' ) );
		$cats        = array_diff( $cats, array( $default_cat ) );

		if ( empty( $cats ) ) {
			return '';
		}

		$name = get_cat_name( (int) reset( $cats ) );
		return $name ? $name : '';
	}

	/**
	 * The actual grid+pagination markup, as a re-usable method so the AJAX
	 * handler (no Elementor settings context, no widget instance from the
	 * page render) can produce byte-identical output to a real page load —
	 * it just needs a fresh instance of this class and these plain values.
	 */
	public function render_archive_content( $posts_per_page, $category, $excerpt_words, $paged, $page_permalink ) {
		ob_start();

		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $posts_per_page,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
			// Several local posts share the exact same post_date (seeded in
			// one batch) — sorting by date alone isn't a stable total order,
			// so LIMIT/OFFSET pagination can repeat or skip posts across
			// pages (confirmed: page 3 was repeating page 1's posts). ID as
			// a tiebreaker guarantees every post appears exactly once.
			'orderby'             => array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
			// Same requirement as related-posts-widget.php and for the same
			// reason: this card design has no fallback for a missing
			// thumbnail, so a post without one (WordPress's default "Hello
			// world!" seed post, most notably) would render a broken-looking
			// empty media block instead of being cleanly excluded.
			'meta_query'          => array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
			// no_found_rows intentionally omitted — pagination needs
			// found_posts/max_num_pages, unlike this project's other blog
			// widgets which don't paginate.
		);
		if ( $category ) {
			$args['category_name'] = sanitize_title( $category );
		}

		$query = new WP_Query( $args );
		if ( ! $query->have_posts() ) {
			return ob_get_clean();
		}
		?>
		<div class="qeema-blog-archive__grid">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php $badge = $this->get_badge_name( get_the_ID() ); ?>
				<a class="qeema-blog-card" href="<?php the_permalink(); ?>">
					<div class="qeema-blog-card__media">
						<?php echo get_the_post_thumbnail( get_the_ID(), 'large' ); ?>
						<?php if ( $badge ) : ?>
							<span class="qeema-blog-archive__badge"><?php echo esc_html( $badge ); ?></span>
						<?php endif; ?>
					</div>
					<div class="qeema-blog-card__body">
						<h3 class="qeema-blog-card__title"><?php the_title(); ?></h3>
						<p class="qeema-blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $excerpt_words ) ); ?></p>
						<span class="qeema-blog-archive__readmore"><?php esc_html_e( 'عرض المزيد »', 'qeematech-elementor-widgets' ); ?></span>
					</div>
				</a>
			<?php endwhile; ?>
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
				) );
				?>
			</nav>
		<?php endif; ?>
		<?php
		wp_reset_postdata();

		return ob_get_clean();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Confirmed empirically on this install: a static Page's
		// '/page-slug/page/N/' pagination sub-URL populates the 'paged'
		// query var (not 'page' — that one is reserved for the
		// <!--nextpage--> in-content pagination feature). 'page' is read as
		// a fallback in case that ever differs under a different rewrite
		// setup. Getting this backwards is a silent bug: pagination links
		// render fine but every page shows the same (page-1) content.
		$paged = max( 1, (int) get_query_var( 'paged' ) ?: (int) get_query_var( 'page' ) );
		// $page_id is captured here (while the actual page is the current
		// queried object) and handed to the JS as a data attribute — the
		// same page_id vs. bare get_permalink() fix applied to
		// portfolio-archive-widget.php after that one was caught pointing
		// pagination links at the wrong URL (get_permalink() with no ID
		// returns whatever post the_post() last set as the global $post,
		// which is the LAST post in this widget's own loop by the time
		// pagination is built, not this archive page itself — and the AJAX
		// handler below has no "current page" context at all to fall back on).
		$page_id        = get_the_ID();
		$page_permalink = get_permalink( $page_id );

		$posts_per_page = ! empty( $settings['posts_per_page'] ) ? intval( $settings['posts_per_page'] ) : 12;
		$category       = ! empty( $settings['category'] ) ? $settings['category'] : '';
		$excerpt_words  = ! empty( $settings['excerpt_words'] ) ? intval( $settings['excerpt_words'] ) : 24;
		?>
		<div class="qeema-blog-archive__ajax" data-qeema-ajax-archive
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-ajax-action="qeema_blog_archive_fetch"
			data-page-id="<?php echo esc_attr( $page_id ); ?>"
			data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>"
			data-category="<?php echo esc_attr( $category ); ?>"
			data-excerpt-words="<?php echo esc_attr( $excerpt_words ); ?>">
			<?php echo $this->render_archive_content( $posts_per_page, $category, $excerpt_words, $paged, $page_permalink ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped internally ?>
		</div>
		<?php
	}
}

// The AJAX endpoint backing this widget's progressive-enhancement swap lives
// in inc/ajax-archive-endpoints.php, not here — see the matching note in
// portfolio-archive-widget.php for why a wp_ajax_* hook registered inside a
// file that's only loaded via Elementor's widget-registration action never
// actually fires on a plain admin-ajax.php request.
