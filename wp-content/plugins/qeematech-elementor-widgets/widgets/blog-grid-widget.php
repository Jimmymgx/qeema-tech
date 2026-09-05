<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Latest-posts carousel, styled to match qeematech.net's "qt-loop-card" blog
 * carousel (dark glass card, cyan border, hover lift, light-sweep shine) so
 * both sites share one visual language for blog cards. Cards use a fixed
 * flex-basis on a scrolling track (same technique as the testimonials
 * carousel) instead of a CSS grid, so they never get squeezed to fit a
 * column count — a grid with more items than columns was the cause of the
 * shrinking cards.
 */
class Qeema_Blog_Grid_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-blog-grid';
	}

	public function get_title() {
		return __( 'Blog Grid', 'qeematech-elementor-widgets' );
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
			'label' => __( 'Blog Grid', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'posts_count', array(
			'label'   => __( 'Number of Posts', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 4,
		) );
		$this->add_control( 'category', array(
			'label'       => __( 'Category Slug (optional)', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'description' => __( 'Leave empty to show the latest posts from any category.', 'qeematech-elementor-widgets' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => ! empty( $settings['posts_count'] ) ? intval( $settings['posts_count'] ) : 4,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		);
		if ( ! empty( $settings['category'] ) ) {
			$args['category_name'] = sanitize_title( $settings['category'] );
		}

		$query = new WP_Query( $args );
		if ( ! $query->have_posts() ) {
			return;
		}
		?>
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
		<?php
	}
}
