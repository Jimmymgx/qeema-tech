<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Testimonials carousel — queries the `testimonial` CPT directly (client
 * image + video link) and renders a dependency-free CSS scroll-snap
 * carousel. Production's version relied on an external CDN (Owl Carousel)
 * loaded on every page regardless of use; this has zero external
 * dependencies and only loads its assets when the widget is actually placed.
 */
class Qeema_Testimonials_Carousel_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-testimonials-carousel';
	}

	public function get_title() {
		return __( 'Testimonials Carousel', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-testimonial-carousel';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-testimonials-carousel' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Content', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'آراء العملاء',
		) );
		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'لقد واصلنا ابداعنا مع مجموعة من أهم رواد الأعمال فى شتى المجالات',
		) );
		$this->add_control( 'items_count', array(
			'label'   => __( 'Number of testimonials to show', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 12,
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$query = new \WP_Query( array(
			'post_type'      => 'testimonial',
			'posts_per_page' => (int) $settings['items_count'],
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		) );
		?>
		<section class="qeema-testimonials">
			<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
			<?php if ( ! empty( $settings['subheading'] ) ) : ?>
				<p><?php echo esc_html( $settings['subheading'] ); ?></p>
			<?php endif; ?>

			<?php if ( $query->have_posts() ) : ?>
				<div class="qeema-testimonials__track">
					<?php while ( $query->have_posts() ) : $query->the_post();
						$image_id  = get_post_meta( get_the_ID(), 'client_image', true );
						$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : get_the_post_thumbnail_url( get_the_ID(), 'medium' );
						$video_url = get_post_meta( get_the_ID(), 'video_link_', true );
						if ( ! $image_url ) {
							continue;
						}
						?>
						<div class="qeema-testimonial-card">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
							<?php if ( $video_url ) : ?>
								<a class="qeema-testimonial-card__play" href="<?php echo esc_url( $video_url ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Play video testimonial', 'qeematech-elementor-widgets' ); ?>">
									<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="12" fill="rgba(0,0,0,.45)"/><polygon points="9,7 17,12 9,17" fill="#fff"/></svg>
								</a>
							<?php endif; ?>
						</div>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
				<div class="qeema-testimonials__nav">
					<button type="button" class="qeema-testimonials__prev" aria-label="<?php esc_attr_e( 'Previous', 'qeematech-elementor-widgets' ); ?>">›</button>
					<button type="button" class="qeema-testimonials__next" aria-label="<?php esc_attr_e( 'Next', 'qeematech-elementor-widgets' ); ?>">‹</button>
				</div>
			<?php else : ?>
				<p style="color:rgba(255,255,255,.5)"><?php esc_html_e( 'No testimonials yet — add some under the Testimonials post type.', 'qeematech-elementor-widgets' ); ?></p>
			<?php endif; ?>
		</section>
		<?php
	}
}
