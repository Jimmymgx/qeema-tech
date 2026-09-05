<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compact sidebar CTA card — replaces the old site's sidebar contact form
 * (WPForms, not installed here) with a card pointing to the real Contact
 * Us page. Deliberately a plain glass card, not the saturated
 * `qeema-cta-banner` treatment — that widget is reserved as a rare,
 * high-contrast closing moment and would look cramped/diluted squeezed
 * into a narrow sidebar column.
 */
class Qeema_Sidebar_Cta_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-sidebar-cta';
	}

	public function get_title() {
		return __( 'Sidebar CTA', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
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
			'default' => 'تواصل معنا',
		) );

		$this->add_control( 'text', array(
			'label'   => __( 'Text', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'هل لديك مشروع تحتاج استشارة فيه؟ فريقنا جاهز لمساعدتك.',
		) );

		$this->add_control( 'button_text', array(
			'label'   => __( 'Button Text', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'تواصل معنا الآن',
		) );

		$this->add_control( 'button_link', array(
			'label'   => __( 'Button Link', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '/أتصل-بنا/' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="qeema-sidebar-cta">
			<div class="qeema-sidebar-cta__icon" aria-hidden="true"><i class="fa fa-comments"></i></div>
			<?php if ( ! empty( $settings['heading'] ) ) : ?>
				<h3><?php echo esc_html( $settings['heading'] ); ?></h3>
			<?php endif; ?>
			<?php if ( ! empty( $settings['text'] ) ) : ?>
				<p><?php echo esc_html( $settings['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $settings['button_text'] ) ) : ?>
				<a class="qt-simple-btn primary"
					<?php echo ! empty( $settings['button_link']['url'] ) ? 'href="' . esc_url( $settings['button_link']['url'] ) . '"' : ''; ?>>
					<?php echo esc_html( $settings['button_text'] ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}
}
