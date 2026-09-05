<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Closing call-to-action banner — a single saturated gradient panel, the one
 * section allowed to break from the site's dark-glass motif since a closing
 * CTA should read as the highest-contrast moment on the page. Built generic
 * (not About-page-specific) so it can be reused as the standard closing
 * pattern on other pages later.
 */
class Qeema_Cta_Banner_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-cta-banner';
	}

	public function get_title() {
		return __( 'CTA Banner', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
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

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'مستعد تبني مشروعك القادم معنا؟',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'لنحوّل فكرتك إلى منتج رقمي حقيقي، بخبرة وشغف وتنفيذ يليق بطموحك.',
		) );

		$this->add_control( 'stat_recap', array(
			'label'   => __( 'Stat recap line (optional, plain text)', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
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
					'default' => 'اطلب مشروعك',
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
						'primary' => __( 'Primary (solid white)', 'qeematech-elementor-widgets' ),
						'ghost'   => __( 'Ghost (translucent outline)', 'qeematech-elementor-widgets' ),
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
		<section class="qeema-cta-banner">
			<div class="qeema-cta-banner__panel qeema-reveal">
				<?php if ( ! empty( $settings['eyebrow'] ) ) : ?>
					<span class="qeema-cta-banner__eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></span>
				<?php endif; ?>
				<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
				<?php if ( ! empty( $settings['subheading'] ) ) : ?>
					<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $settings['buttons'] ) ) : ?>
					<div class="qeema-cta-banner__actions">
						<?php foreach ( $settings['buttons'] as $button ) : ?>
							<a class="qeema-cta-banner__btn <?php echo esc_attr( $button['style'] ); ?>"
								<?php echo ! empty( $button['link']['url'] ) ? 'href="' . esc_url( $button['link']['url'] ) . '"' : ''; ?>>
								<?php echo esc_html( $button['text'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $settings['stat_recap'] ) ) : ?>
					<p class="qeema-cta-banner__recap"><?php echo esc_html( $settings['stat_recap'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
