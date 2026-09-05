<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sticky sidebar "latest posts" list for the Blog page — a plain vertical
 * list (small thumbnail + title + date only, no excerpt/badge), matching the
 * old site's "أحدث المقالات" sidebar exactly. Deliberately not paginated;
 * this is always just the N most recent posts.
 */
class Qeema_Blog_Sidebar_Latest_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-blog-sidebar-latest';
	}

	public function get_title() {
		return __( 'Blog Sidebar — Latest Posts', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Latest Posts', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'أحدث المقالات',
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number of Posts', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 5,
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$query = new WP_Query( array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => ! empty( $settings['count'] ) ? intval( $settings['count'] ) : 5,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );

		if ( ! $query->have_posts() ) {
			return;
		}
		?>
		<div class="qeema-blog-sidebar-latest">
			<?php if ( ! empty( $settings['heading'] ) ) : ?>
				<h5><?php echo esc_html( $settings['heading'] ); ?></h5>
			<?php endif; ?>
			<ul class="qeema-blog-sidebar-latest__list">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<li class="qeema-blog-sidebar-latest__item">
						<a class="qeema-blog-sidebar-latest__link" href="<?php the_permalink(); ?>">
							<span class="qeema-blog-sidebar-latest__thumb"><?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail' ); ?></span>
							<span class="qeema-blog-sidebar-latest__meta">
								<span class="qeema-blog-sidebar-latest__title"><?php the_title(); ?></span>
								<span class="qeema-blog-sidebar-latest__date"><?php echo esc_html( get_the_date() ); ?></span>
							</span>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>
		</div>
		<?php
		wp_reset_postdata();
	}
}
