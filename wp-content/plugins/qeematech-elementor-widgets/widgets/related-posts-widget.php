<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Related articles" carousel for the single-post template. Reuses the
 * blog-grid widget's exact card markup/CSS/JS (`.qeema-blog-carousel`,
 * `.qeema-blog-card`) for visual consistency, but with different query
 * logic: same-category posts first, topped up with latest-other-posts so
 * the section is never sparse just because a category is small.
 */
class Qeema_Related_Posts_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-related-posts';
	}

	public function get_title() {
		return __( 'Related Posts', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-blog-carousel' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Related Posts', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'مقالات ذات صلة',
		) );

		$this->add_control( 'posts_count', array(
			'label'   => __( 'Number of Posts', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 6,
		) );

		$this->end_controls_section();
	}

	private function get_related_ids( $current_id, $count ) {
		// related posts don't need to be live-fresh — cache the ID list for an
		// hour so repeat views of the same post skip both queries below
		$cache_key = 'qeema_related_ids_' . $current_id . '_' . $count;
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$default_cat = (int) get_option( 'default_category' );
		$cats        = wp_get_post_categories( $current_id, array( 'fields' => 'ids' ) );
		$cats        = array_diff( $cats, array( $default_cat ) );

		$related_ids = array();

		// Require a featured image — every real post has one; excludes
		// WordPress's generic "Hello world!" seed post (and anything else
		// without one) from ever surfacing as a "related" card, since the
		// card design has no fallback for a missing thumbnail.
		$has_thumbnail = array(
			'key'     => '_thumbnail_id',
			'compare' => 'EXISTS',
		);

		if ( ! empty( $cats ) ) {
			$same_cat = new WP_Query( array(
				'post_type'           => 'post',
				'posts_per_page'      => $count,
				'post_status'         => 'publish',
				'post__not_in'        => array( $current_id ),
				'category__in'        => array_values( $cats ),
				'meta_query'          => array( $has_thumbnail ),
				'ignore_sticky_posts' => true,
				'fields'              => 'ids',
				'no_found_rows'       => true,
			) );
			$related_ids = $same_cat->posts;
		}

		if ( count( $related_ids ) < $count ) {
			$fallback = new WP_Query( array(
				'post_type'           => 'post',
				'posts_per_page'      => $count - count( $related_ids ),
				'post_status'         => 'publish',
				'post__not_in'        => array_merge( array( $current_id ), $related_ids ),
				'meta_query'          => array( $has_thumbnail ),
				'ignore_sticky_posts' => true,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'fields'              => 'ids',
				'no_found_rows'       => true,
			) );
			$related_ids = array_merge( $related_ids, $fallback->posts );
		}

		set_transient( $cache_key, $related_ids, HOUR_IN_SECONDS );

		return $related_ids;
	}

	protected function render() {
		$settings    = $this->get_settings_for_display();
		$count       = ! empty( $settings['posts_count'] ) ? intval( $settings['posts_count'] ) : 6;
		$current_id  = get_the_ID();
		$related_ids = $this->get_related_ids( $current_id, $count );

		if ( empty( $related_ids ) ) {
			return;
		}

		$query = new WP_Query( array(
			'post_type'     => 'post',
			'post__in'      => $related_ids,
			'orderby'       => 'post__in',
			'no_found_rows' => true,
		) );
		?>
		<section class="qeema-related-posts">
			<div class="qeema-related-posts__head">
				<?php if ( ! empty( $settings['heading'] ) ) : ?>
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
				<?php endif; ?>
			</div>
			<div class="qeema-blog-carousel">
				<div class="qeema-blog-carousel__track" data-cursor="<?php esc_attr_e( 'اسحب للتصفح', 'qeematech-elementor-widgets' ); ?>">
					<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						<a class="qeema-blog-card" href="<?php the_permalink(); ?>" data-cursor="<?php esc_attr_e( 'قراءة', 'qeematech-elementor-widgets' ); ?>">
							<div class="qeema-blog-card__media">
								<?php echo get_the_post_thumbnail( get_the_ID(), 'large' ); ?>
							</div>
							<div class="qeema-blog-card__body">
								<h3 class="qeema-blog-card__title"><?php the_title(); ?></h3>
								<p class="qeema-blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
							</div>
						</a>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
				<div class="qeema-blog-carousel__progress"><span class="qeema-blog-carousel__progress-bar"></span></div>
			</div>
		</section>
		<?php
	}
}
