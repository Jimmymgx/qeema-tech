<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Floating "browser window" screenshot showcase — the visual centerpiece for
 * service pages that need to show real website work rather than a generic
 * decoration. Kept as its own widget (not a new hero-section visual_variant)
 * so the shared hero widget used sitewide is never at risk of being affected
 * by this. Visually it's the exact same glow+float+chip recipe already
 * proven by the hero's `phone_mockups` visual, just adapted to a wide
 * browser-chrome frame instead of a tall phone frame.
 */
class Qeema_Browser_Showcase_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-browser-showcase';
	}

	public function get_title() {
		return __( 'Browser Mockup Showcase', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-website-favicon';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'header_section', array(
			'label' => __( 'Header (optional)', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'screens_section', array(
			'label' => __( 'Browser Screens', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'screenshots', array(
			'label'       => __( 'Screenshots (first 3 are used)', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'image',
					'label'   => __( 'Image', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::MEDIA,
					'default' => array(
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					),
				),
				array(
					'name'    => 'url_label',
					'label'   => __( 'Fake URL bar text', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'qeematech.net',
				),
			),
			'default'     => array(),
			'title_field' => '{{{ url_label }}}',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'chips_section', array(
			'label' => __( 'Floating Chips', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'chips', array(
			'label'       => __( 'Chips (first 3 are used)', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'text',
					'label'   => __( 'Text', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'Chip',
				),
			),
			'default'     => array(),
			'title_field' => '{{{ text }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings    = $this->get_settings_for_display();
		$screenshots = array_slice( $settings['screenshots'], 0, 3 );
		$chips       = array_slice( $settings['chips'], 0, 3 );
		$slots       = array( 'a', 'b', 'c' );
		?>
		<section class="qeema-browser-showcase">
			<?php if ( ! empty( $settings['heading'] ) ) : ?>
				<div class="qeema-browser-showcase__head">
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="qeema-browser-showcase__stage">
				<div class="qeema-browser-showcase__glow"></div>

				<?php foreach ( $screenshots as $index => $shot ) :
					$slot = $slots[ $index ] ?? 'a';
					if ( empty( $shot['image']['url'] ) || \Elementor\Utils::get_placeholder_image_src() === $shot['image']['url'] ) {
						continue;
					}
					?>
					<div class="qeema-browser-mock browser-<?php echo esc_attr( $slot ); ?>">
						<div class="qeema-browser-mock__bar">
							<span class="qt-dot r"></span>
							<span class="qt-dot y"></span>
							<span class="qt-dot g"></span>
							<span class="qeema-browser-mock__url"><?php echo esc_html( $shot['url_label'] ); ?></span>
						</div>
						<div class="qeema-browser-mock__screen" style="background-image:url('<?php echo esc_url( $shot['image']['url'] ); ?>')"></div>
					</div>
				<?php endforeach; ?>

				<?php foreach ( $chips as $index => $chip ) :
					$slot = $slots[ $index ] ?? 'a';
					if ( empty( $chip['text'] ) ) {
						continue;
					}
					?>
					<span class="qt-float-chip qeema-browser-showcase__chip qeema-browser-showcase__chip--<?php echo esc_attr( $slot ); ?>"><?php echo esc_html( $chip['text'] ); ?></span>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
