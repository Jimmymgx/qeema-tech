<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Success-state hero for the post-submission thank-you page — the one place
 * on the site with a distinct visual job (celebrate a completed action)
 * rather than pitch a service. Checkmark + glow ring stay inside the site's
 * own accent palette instead of a generic green "success" color so the page
 * still reads as part of the brand, not a stock form-plugin confirmation.
 */
class Qeema_Thank_You_Success_Hero_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-thank-you-hero';
	}

	public function get_title() {
		return __( 'Thank You Success Hero', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-check-circle-o';
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

		$this->add_control( 'badge', array(
			'label'   => __( 'Badge', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'تم إرسال طلبك بنجاح',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'شكرًا لك، سيتم التواصل معك خلال ساعات',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'buttons_section', array(
			'label' => __( 'Buttons', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'buttons', array(
			'label'       => __( 'Buttons', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'text',
					'label'   => __( 'Text', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'تواصل واتساب الآن',
				),
				array(
					'name'  => 'link',
					'label' => __( 'Link', 'qeematech-elementor-widgets' ),
					'type'  => \Elementor\Controls_Manager::URL,
				),
				array(
					'name'    => 'style',
					'label'   => __( 'Style', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'primary',
					'options' => array(
						'primary' => __( 'Primary', 'qeematech-elementor-widgets' ),
						'ghost'   => __( 'Ghost', 'qeematech-elementor-widgets' ),
					),
				),
			),
			'default'     => array(),
			'title_field' => '{{{ text }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="qeema-thank-you-hero">
			<div class="qeema-thank-you-hero__wrap qeema-reveal">
				<div class="qeema-thank-you-hero__mark">
					<span class="qeema-thank-you-hero__ring qeema-thank-you-hero__ring--1"></span>
					<span class="qeema-thank-you-hero__ring qeema-thank-you-hero__ring--2"></span>
					<span class="qeema-thank-you-hero__check"><i class="fas fa-check" aria-hidden="true"></i></span>
				</div>

				<?php if ( ! empty( $settings['badge'] ) ) : ?>
					<span class="qeema-thank-you-hero__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
				<?php endif; ?>

				<h1 class="qeema-thank-you-hero__heading"><?php echo wp_kses_post( $settings['heading'] ); ?></h1>

				<?php if ( ! empty( $settings['subheading'] ) ) : ?>
					<p class="qeema-thank-you-hero__subheading"><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $settings['buttons'] ) ) : ?>
					<div class="qeema-thank-you-hero__actions">
						<?php foreach ( $settings['buttons'] as $button ) : ?>
							<a class="qt-simple-btn <?php echo esc_attr( $button['style'] ); ?>"
								<?php echo ! empty( $button['link']['url'] ) ? 'href="' . esc_url( $button['link']['url'] ) . '"' : ''; ?>>
								<?php echo esc_html( $button['text'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
