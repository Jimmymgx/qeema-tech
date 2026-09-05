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
			'label'       => __( 'Number of testimonials to show', 'qeematech-elementor-widgets' ),
			'description' => __( '-1 shows all published testimonials.', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'default'     => -1,
		) );
		$this->add_control( 'layout', array(
			'label'   => __( 'Layout', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'carousel',
			'options' => array(
				'carousel' => __( 'Carousel (auto-scroll)', 'qeematech-elementor-widgets' ),
				'grid'     => __( 'Grid (show all at once)', 'qeematech-elementor-widgets' ),
			),
		) );
		$this->end_controls_section();
	}

	/**
	 * Works out how a stored video URL should be played in the popup: a
	 * YouTube/Facebook link needs an embeddable iframe URL, while a
	 * self-hosted mp4 (the copied-over old testimonial uploads) can just be
	 * played directly in a <video> tag.
	 */
	private function get_video_embed( $video_url ) {
		if ( preg_match( '#(?:youtube\.com/(?:watch\?v=|shorts/)|youtu\.be/)([A-Za-z0-9_-]+)#', $video_url, $m ) ) {
			return array( 'type' => 'youtube', 'src' => 'https://www.youtube.com/embed/' . $m[1] . '?autoplay=1&rel=0' );
		}
		if ( false !== strpos( $video_url, 'facebook.com' ) ) {
			return array( 'type' => 'facebook', 'src' => 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode( $video_url ) . '&show_text=false&autoplay=true' );
		}
		return array( 'type' => 'video', 'src' => $video_url );
	}

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$is_grid   = 'grid' === ( $settings['layout'] ?? 'carousel' );
		$wrap_class = $is_grid ? 'qeema-testimonials__grid' : 'qeema-testimonials__track';

		$query = new \WP_Query( array(
			'post_type'      => 'testimonial',
			'posts_per_page' => (int) $settings['items_count'],
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		) );
		?>
		<section class="qeema-testimonials">
			<span class="qeema-testimonials__eyebrow"><?php esc_html_e( 'قصص نجاح حقيقية', 'qeematech-elementor-widgets' ); ?></span>
			<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
			<?php if ( ! empty( $settings['subheading'] ) ) : ?>
				<p><?php echo esc_html( $settings['subheading'] ); ?></p>
			<?php endif; ?>

			<?php if ( $query->have_posts() ) : ?>
				<div class="<?php echo esc_attr( $wrap_class ); ?>" <?php if ( ! $is_grid ) : ?>data-cursor="<?php esc_attr_e( 'اسحب للتصفح', 'qeematech-elementor-widgets' ); ?>"<?php endif; ?>>
					<?php while ( $query->have_posts() ) : $query->the_post();
						$image_id  = get_post_meta( get_the_ID(), 'client_image', true );
						$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : get_the_post_thumbnail_url( get_the_ID(), 'medium' );
						$video_url = get_post_meta( get_the_ID(), 'video_link_', true );
						if ( ! $image_url ) {
							continue;
						}
						$embed = $video_url ? $this->get_video_embed( $video_url ) : null;
						?>
						<div class="qeema-testimonial-card">
							<img src="<?php echo esc_url( $image_url ); ?>" class="qeema-testimonial-card__bg" alt="" aria-hidden="true" loading="lazy">
							<img src="<?php echo esc_url( $image_url ); ?>" class="qeema-testimonial-card__fg" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="sync">
							<?php if ( $embed ) : ?>
								<button type="button" class="qeema-testimonial-card__play" data-video-type="<?php echo esc_attr( $embed['type'] ); ?>" data-video-src="<?php echo esc_attr( $embed['src'] ); ?>" data-cursor="<?php esc_attr_e( 'مشاهدة', 'qeematech-elementor-widgets' ); ?>" aria-label="<?php esc_attr_e( 'Play video testimonial', 'qeematech-elementor-widgets' ); ?>">
									<span class="qeema-testimonial-card__ring">
										<svg viewBox="0 0 24 24"><polygon points="9,7 18,12 9,17" fill="currentColor"/></svg>
									</span>
									<span class="qeema-testimonial-card__label"><?php esc_html_e( 'مشاهدة الفيديو', 'qeematech-elementor-widgets' ); ?></span>
								</button>
							<?php endif; ?>
						</div>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
				<?php if ( ! $is_grid ) : ?>
					<div class="qeema-testimonials__progress"><span class="qeema-testimonials__progress-bar"></span></div>
				<?php endif; ?>

				<div class="qeema-video-modal" aria-hidden="true">
					<div class="qeema-video-modal__backdrop"></div>
					<div class="qeema-video-modal__inner">
						<button type="button" class="qeema-video-modal__close" aria-label="<?php esc_attr_e( 'Close', 'qeematech-elementor-widgets' ); ?>">&times;</button>
						<div class="qeema-video-modal__media"></div>
					</div>
				</div>
			<?php else : ?>
				<p style="color:rgba(255,255,255,.5)"><?php esc_html_e( 'No testimonials yet — add some under the Testimonials post type.', 'qeematech-elementor-widgets' ); ?></p>
			<?php endif; ?>
		</section>
		<?php
	}
}
