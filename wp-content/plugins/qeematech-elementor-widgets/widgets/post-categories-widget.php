<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plain categories list for the single-post sidebar. Built as its own tiny
 * widget instead of using Elementor's native "WordPress" widget wrapper
 * (wp-widget-categories) — that wrapper unconditionally pulls in Elementor's
 * `swiper` JS/CSS (its `get_script_depends()`/`get_style_depends()` return
 * swiper unconditionally, regardless of which underlying WP widget it
 * wraps), a real dependency this site otherwise never loads anywhere.
 */
class Qeema_Post_Categories_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-post-categories';
	}

	public function get_title() {
		return __( 'Post Categories List', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-archive-posts';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Content', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'الفئات',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$categories = get_categories( array(
			'hide_empty' => true,
			'exclude'    => (int) get_option( 'default_category' ),
		) );

		if ( empty( $categories ) ) {
			return;
		}
		?>
		<div class="qeema-post-categories">
			<?php if ( ! empty( $settings['heading'] ) ) : ?>
				<h5><?php echo esc_html( $settings['heading'] ); ?></h5>
			<?php endif; ?>
			<ul>
				<?php foreach ( $categories as $cat ) : ?>
					<li>
						<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>">
							<?php echo esc_html( $cat->name ); ?> (<?php echo (int) $cat->count; ?>)
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
