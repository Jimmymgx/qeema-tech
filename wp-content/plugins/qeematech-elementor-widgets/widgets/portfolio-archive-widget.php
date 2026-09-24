<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paginated portfolio archive — the أعمالنا page's main section: taxonomy
 * filter tabs + a real paginated grid. Card markup matches
 * portfolio-teaser-widget.php (glass cards, desc, dual CTAs). Pagination is
 * progressive: real /page/N/ links remain for no-JS, while ajax-archive.js
 * upgrades them to infinite scroll (append on scroll / "عرض المزيد").
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

		$this->add_control( 'badge', array(
			'label'   => __( 'Badge', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'معرض الأعمال',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'قصص نجاح تقنية صنعناها لشركائنا',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'استكشف سابقة أعمالنا وتعرف على كيفية تحويل أفكار عملائنا إلى منصات رقمية تتصدر المنافسة في مختلف القطاعات.',
		) );

		$this->add_control( 'posts_per_page', array(
			'label'   => __( 'Posts Per Page (batch)', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 8,
		) );

		$this->add_control( 'all_label', array(
			'label'   => __( '"All" Tab Label', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'الكل',
		) );

		$this->add_control( 'meta_note', array(
			'label'   => __( 'Meta Note', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'أعمال حقيقية قيد التشغيل',
		) );

		$this->add_control( 'load_more_text', array(
			'label'   => __( 'Load More Label', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'عرض المزيد',
		) );

		$this->add_control( 'locked_category', array(
			'label'       => __( 'Lock To Category Slug (optional)', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'description' => __( 'When set, the grid always shows only this category and the filter tabs are hidden entirely — for single-category landing pages (e.g. one of the أعمالنا category pages) rather than the main archive.', 'qeematech-elementor-widgets' ),
		) );

		$this->end_controls_section();
	}

	private function get_current_cat_from_request() {
		if ( empty( $_GET['cat'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public read-only filter, not a state change
			return '';
		}
		return sanitize_title( wp_unslash( $_GET['cat'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}

	private function build_query_args( $posts_per_page, $paged, $effective_cat ) {
		$args = array(
			'post_type'           => 'portfolio',
			'post_status'         => 'publish',
			'posts_per_page'      => $posts_per_page,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
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
		return $args;
	}

	private function render_filters( $terms, $current_cat, $all_label, $page_permalink ) {
		?>
		<nav class="qeema-portfolio-archive__filters" aria-label="<?php esc_attr_e( 'تصفية الأعمال', 'qeematech-elementor-widgets' ); ?>">
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

	private function card_desc( $post_id ) {
		$excerpt = get_the_excerpt( $post_id );
		if ( $excerpt ) {
			return wp_trim_words( wp_strip_all_tags( $excerpt ), 12, '…' );
		}
		$challenge = function_exists( 'get_field' ) ? get_field( 'التحدي', $post_id ) : '';
		if ( is_string( $challenge ) && $challenge ) {
			return wp_trim_words( wp_strip_all_tags( $challenge ), 12, '…' );
		}
		$terms = get_the_terms( $post_id, 'portfolio-categories' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->name;
		}
		return '';
	}

	/**
	 * Render only the grid item nodes for a page (used by AJAX append).
	 *
	 * @return array{html:string,found:int,max_pages:int,rendered:int}
	 */
	public function render_archive_items( $posts_per_page, $paged, $current_cat, $locked_category = '' ) {
		$effective_cat = $locked_category ? $locked_category : $current_cat;
		$query         = new WP_Query( $this->build_query_args( $posts_per_page, $paged, $effective_cat ) );
		ob_start();
		$rendered = 0;

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id  = get_the_ID();
				$image_id = get_post_thumbnail_id( $post_id );
				if ( ! $image_id ) {
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
				$is_app   = in_array( 'تطبيقات-الهاتف', $cats, true );
				$item_cls = 'qeema-portfolio-grid__item' . ( $is_app ? ' qeema-portfolio-grid__item--app' : '' );
				$rendered++;
				?>
				<div class="<?php echo esc_attr( $item_cls ); ?>" data-cats="<?php echo esc_attr( implode( ' ', $cats ) ); ?>">
					<?php echo $is_app ? $this->render_phone_card( $post_id, $image_id ) : $this->render_browser_card( $post_id, $image_id ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped internally ?>
				</div>
				<?php
			}
		}
		wp_reset_postdata();

		return array(
			'html'      => ob_get_clean(),
			'found'     => (int) $query->found_posts,
			'max_pages' => (int) $query->max_num_pages,
			'rendered'  => $rendered,
		);
	}

	/**
	 * Full filters + grid + load-more markup (page load + filter AJAX replace).
	 */
	public function render_archive_content( $posts_per_page, $all_label, $paged, $current_cat, $page_permalink, $locked_category = '', $meta = array() ) {
		ob_start();

		$badge          = isset( $meta['badge'] ) ? (string) $meta['badge'] : '';
		$heading        = isset( $meta['heading'] ) ? (string) $meta['heading'] : '';
		$subheading     = isset( $meta['subheading'] ) ? (string) $meta['subheading'] : '';
		$meta_note      = isset( $meta['meta_note'] ) ? (string) $meta['meta_note'] : '';
		$load_more_text = isset( $meta['load_more_text'] ) ? (string) $meta['load_more_text'] : 'عرض المزيد';
		$show_head      = ! empty( $meta['show_head'] );

		$effective_cat = $locked_category ? $locked_category : $current_cat;
		$query         = new WP_Query( $this->build_query_args( $posts_per_page, $paged, $effective_cat ) );

		if ( $show_head && ( $badge || $heading || $subheading ) ) {
			?>
			<header class="qeema-portfolio-archive__head">
				<?php if ( $badge ) : ?>
					<span class="qeema-portfolio-archive__badge">
						<span class="qeema-portfolio-archive__badge-icon" aria-hidden="true"></span>
						<?php echo esc_html( $badge ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="qeema-portfolio-archive__title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<p class="qeema-portfolio-archive__lead"><?php echo esc_html( $subheading ); ?></p>
				<?php endif; ?>
			</header>
			<?php
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

		if ( ! $query->have_posts() ) {
			?>
			<p class="qeema-portfolio-archive__empty"><?php esc_html_e( 'لا توجد أعمال في هذا القسم حالياً.', 'qeematech-elementor-widgets' ); ?></p>
			<?php
			return ob_get_clean();
		}

		$max_pages = (int) $query->max_num_pages;
		?>
		<div class="qeema-portfolio-grid" data-qeema-archive-grid data-page="<?php echo esc_attr( (string) $paged ); ?>" data-max-pages="<?php echo esc_attr( (string) $max_pages ); ?>">
			<div class="qeema-portfolio-grid__meta">
				<?php if ( $meta_note ) : ?>
					<p class="qeema-portfolio-grid__meta-note">
						<span class="qeema-portfolio-grid__meta-dot" aria-hidden="true"></span>
						<?php echo esc_html( $meta_note ); ?>
					</p>
				<?php endif; ?>
			</div>
			<div class="qeema-portfolio-grid__wrap">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$post_id  = get_the_ID();
					$image_id = get_post_thumbnail_id( $post_id );
					if ( ! $image_id ) {
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
					$is_app   = in_array( 'تطبيقات-الهاتف', $cats, true );
					$item_cls = 'qeema-portfolio-grid__item' . ( $is_app ? ' qeema-portfolio-grid__item--app' : '' );
					?>
					<div class="<?php echo esc_attr( $item_cls ); ?>" data-cats="<?php echo esc_attr( implode( ' ', $cats ) ); ?>">
						<?php echo $is_app ? $this->render_phone_card( $post_id, $image_id ) : $this->render_browser_card( $post_id, $image_id ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped internally ?>
					</div>
				<?php endwhile; ?>
			</div>
		</div>

		<?php if ( $max_pages > 1 ) : ?>
			<?php
			$next_page = $paged + 1;
			$has_more  = $paged < $max_pages;
			$next_url  = '';
			if ( $has_more ) {
				$next_url = trailingslashit( $page_permalink ) . 'page/' . $next_page . '/';
				if ( ! $locked_category && $current_cat ) {
					$next_url = add_query_arg( 'cat', urldecode( $current_cat ), $next_url );
				}
			}
			?>
			<div class="qeema-portfolio-archive__more<?php echo $has_more ? '' : ' is-done'; ?>"
				data-qeema-infinite-more
				data-next-url="<?php echo esc_url( $next_url ); ?>"
				data-next-page="<?php echo esc_attr( (string) $next_page ); ?>"
				data-max-pages="<?php echo esc_attr( (string) $max_pages ); ?>"
				<?php echo $has_more ? '' : ' hidden'; ?>>
				<button type="button" class="qeema-portfolio-teaser__btn primary qeema-portfolio-archive__load-btn">
					<?php echo esc_html( $load_more_text ); ?>
					<span aria-hidden="true">←</span>
				</button>
				<p class="qeema-portfolio-archive__loading" hidden><?php esc_html_e( 'جاري تحميل المزيد…', 'qeematech-elementor-widgets' ); ?></p>
				<noscript>
					<nav class="qeema-blog-pagination">
						<?php
						echo paginate_links( array( // phpcs:ignore WordPress.Security.EscapeOutput -- paginate_links() output is already escaped
							'base'      => trailingslashit( $page_permalink ) . '%_%',
							'format'    => 'page/%#%/',
							'current'   => $paged,
							'total'     => $max_pages,
							'prev_text' => '‹',
							'next_text' => '›',
							'type'      => 'plain',
							'add_args'  => ( ! $locked_category && $current_cat ) ? array( 'cat' => urldecode( $current_cat ) ) : array(),
						) );
						?>
					</nav>
				</noscript>
			</div>
		<?php endif; ?>
		<?php
		wp_reset_postdata();

		return ob_get_clean();
	}

	private function render_browser_card( $post_id, $image_id ) {
		$permalink = get_permalink( $post_id );
		$external  = function_exists( 'get_field' ) ? get_field( 'link', $post_id ) : '';
		$desc      = $this->card_desc( $post_id );
		$domain    = '';
		if ( $external ) {
			$host   = wp_parse_url( $external, PHP_URL_HOST );
			$domain = $host ? preg_replace( '/^www\./', '', $host ) : '';
		}
		ob_start();
		?>
		<article class="qeema-portfolio-grid__card qeema-portfolio-grid__browser-card">
			<div class="qeema-portfolio-grid__stage">
				<div class="qeema-portfolio-grid__browser">
					<div class="qeema-portfolio-grid__browser-bar">
						<span class="qt-dot r"></span><span class="qt-dot y"></span><span class="qt-dot g"></span>
						<?php if ( $domain ) : ?>
							<span class="qeema-portfolio-grid__browser-url"><?php echo esc_html( $domain ); ?></span>
						<?php endif; ?>
					</div>
					<a class="qeema-portfolio-grid__browser-screen" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
						<?php
						echo wp_get_attachment_image( $image_id, 'large', false, array(
							'class'    => 'qeema-portfolio-grid__media',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'sizes'    => '(max-width:820px) 45vw, 280px',
							'alt'      => get_the_title( $post_id ),
						) );
						?>
					</a>
				</div>
			</div>
			<div class="qeema-portfolio-grid__browser-footer">
				<h3 class="qeema-portfolio-grid__browser-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<?php if ( $desc ) : ?>
					<p class="qeema-portfolio-grid__browser-desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
				<div class="qeema-portfolio-grid__browser-actions">
					<a class="qeema-portfolio-grid__browser-btn ghost" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'عرض مشروع %s', 'qeematech-elementor-widgets' ), get_the_title( $post_id ) ) ); ?>"><?php esc_html_e( 'عرض المشروع', 'qeematech-elementor-widgets' ); ?></a>
				</div>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}

	private function render_phone_card( $post_id, $image_id ) {
		$permalink = get_permalink( $post_id );
		$android   = function_exists( 'get_field' ) ? get_field( 'android', $post_id ) : '';
		$ios       = function_exists( 'get_field' ) ? get_field( 'ios', $post_id ) : '';
		$store_url = $android ? $android : $ios;
		$desc      = $this->card_desc( $post_id );
		ob_start();
		?>
		<article class="qeema-portfolio-grid__card qeema-portfolio-grid__phone-card">
			<div class="qeema-portfolio-grid__stage">
				<a class="qeema-portfolio-grid__shot" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
					<?php
					echo wp_get_attachment_image( $image_id, 'large', false, array(
						'class'    => 'qeema-portfolio-grid__media',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => '(max-width:560px) 70vw, 220px',
						'alt'      => get_the_title( $post_id ),
					) );
					?>
				</a>
			</div>
			<div class="qeema-portfolio-grid__browser-footer">
				<h3 class="qeema-portfolio-grid__browser-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<?php if ( $desc ) : ?>
					<p class="qeema-portfolio-grid__browser-desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
				<div class="qeema-portfolio-grid__browser-actions">
					<a class="qeema-portfolio-grid__browser-btn ghost" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'عرض مشروع %s', 'qeematech-elementor-widgets' ), get_the_title( $post_id ) ) ); ?>"><?php esc_html_e( 'عرض المشروع', 'qeematech-elementor-widgets' ); ?></a>
				</div>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$paged       = max( 1, (int) get_query_var( 'paged' ) ?: (int) get_query_var( 'page' ) );
		$current_cat = $this->get_current_cat_from_request();
		$page_id        = get_the_ID();
		$page_permalink = get_permalink( $page_id );

		$posts_per_page  = ! empty( $settings['posts_per_page'] ) ? intval( $settings['posts_per_page'] ) : 8;
		$all_label       = ! empty( $settings['all_label'] ) ? $settings['all_label'] : 'الكل';
		$locked_category = ! empty( $settings['locked_category'] ) ? sanitize_title( $settings['locked_category'] ) : '';

		$meta = array(
			'show_head'      => true,
			'badge'          => $settings['badge'] ?? '',
			'heading'        => $settings['heading'] ?? '',
			'subheading'     => $settings['subheading'] ?? '',
			'meta_note'      => $settings['meta_note'] ?? '',
			'load_more_text' => $settings['load_more_text'] ?? 'عرض المزيد',
		);
		?>
		<section class="qeema-portfolio-archive">
			<div class="qeema-portfolio-archive__wrap">
				<div class="qeema-portfolio-archive__ajax" data-qeema-ajax-archive data-qeema-infinite-archive
					data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
					data-ajax-action="qeema_portfolio_archive_fetch"
					data-page-id="<?php echo esc_attr( $page_id ); ?>"
					data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>"
					data-all-label="<?php echo esc_attr( $all_label ); ?>"
					data-locked-category="<?php echo esc_attr( $locked_category ); ?>"
					data-badge="<?php echo esc_attr( $meta['badge'] ); ?>"
					data-heading="<?php echo esc_attr( $meta['heading'] ); ?>"
					data-subheading="<?php echo esc_attr( $meta['subheading'] ); ?>"
					data-meta-note="<?php echo esc_attr( $meta['meta_note'] ); ?>"
					data-load-more-text="<?php echo esc_attr( $meta['load_more_text'] ); ?>">
					<?php echo $this->render_archive_content( $posts_per_page, $all_label, $paged, $current_cat, $page_permalink, $locked_category, $meta ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped internally ?>
				</div>
			</div>
		</section>
		<?php
	}
}
