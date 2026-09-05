<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A single oversized brand-voice statement — no badge, no card, no grid.
 * Meant as a deliberate "palate cleanser" between structured sections on
 * pages like About Us, where every other section is a heading+grid.
 */
class Qeema_Pull_Quote_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-pull-quote';
	}

	public function get_title() {
		return __( 'Pull Quote', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-blockquote';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-scroll-reveal' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Content', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		) );

		$this->add_control( 'quote', array(
			'label'   => __( 'Quote', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->add_control( 'attribution', array(
			'label'   => __( 'Attribution (optional)', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['quote'] ) ) {
			return;
		}
		?>
		<section class="qeema-pull-quote">
			<div class="qeema-pull-quote__panel qeema-reveal">
				<div class="qeema-pull-quote__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="currentColor"><path d="M7.17 6C4.87 8 3.5 10.9 3.5 14.1c0 3.2 2.1 5.4 4.9 5.4 2.4 0 4.2-1.8 4.2-4.1 0-2.2-1.6-3.9-3.7-3.9-.4 0-.8.1-1.1.2.3-1.8 1.6-3.6 3.5-4.8L7.17 6Zm9.6 0c-2.3 2-3.67 4.9-3.67 8.1 0 3.2 2.1 5.4 4.9 5.4 2.4 0 4.2-1.8 4.2-4.1 0-2.2-1.6-3.9-3.7-3.9-.4 0-.8.1-1.1.2.3-1.8 1.6-3.6 3.5-4.8L16.77 6Z"/></svg>
				</div>
				<?php if ( ! empty( $settings['eyebrow'] ) ) : ?>
					<span class="qeema-pull-quote__eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></span>
				<?php endif; ?>
				<blockquote class="qeema-pull-quote__text"><?php echo wp_kses_post( nl2br( esc_html( $settings['quote'] ) ) ); ?></blockquote>
				<?php if ( ! empty( $settings['attribution'] ) ) : ?>
					<cite class="qeema-pull-quote__cite"><?php echo esc_html( $settings['attribution'] ); ?></cite>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
