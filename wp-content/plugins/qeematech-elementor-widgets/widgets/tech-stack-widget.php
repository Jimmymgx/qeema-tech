<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Technology-stack showcase — a simple icon+label tile grid for pages that
 * need to list concrete tools/platforms (e.g. a service page's "what we
 * build with" section). Kept as its own widget rather than adapting
 * trusted-by-logos-widget.php, which pulls real client logos from an ACF
 * options page and has no per-item icon/label repeater of its own.
 */
class Qeema_Tech_Stack_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-tech-stack';
	}

	public function get_title() {
		return __( 'Technology Stack', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-code';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-scroll-reveal' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'header_section', array(
			'label' => __( 'Header', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'badge', array(
			'label'   => __( 'Badge', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'التقنيات التي نعمل بها',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'items_section', array(
			'label' => __( 'Technologies', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'items', array(
			'label'       => __( 'Items', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'icon_text',
					'label'   => __( 'Icon (Font Awesome class, e.g. "fab fa-wordpress")', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'fab fa-wordpress',
				),
				array(
					'name'    => 'label',
					'label'   => __( 'Label', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'WordPress',
				),
			),
			'default'     => array(),
			'title_field' => '{{{ label }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="qeema-tech-stack">
			<div class="qeema-tech-stack__wrap">
				<div class="qeema-tech-stack__head">
					<?php if ( ! empty( $settings['badge'] ) ) : ?>
						<span class="qeema-tech-stack__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
					<?php endif; ?>
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="qeema-tech-stack__grid">
					<?php foreach ( $settings['items'] as $index => $item ) :
						$delay        = esc_attr( $index * 0.08 );
						$accent_class = 'accent-' . ( ( $index % 3 ) + 1 );
						?>
						<div class="qeema-tech-stack__item <?php echo esc_attr( $accent_class ); ?> qeema-reveal" style="--reveal-delay:<?php echo $delay; ?>s;">
							<div class="qeema-tech-stack__icon"><?php
								$icon_value = trim( $item['icon_text'] );
								if ( preg_match( '/^(fas|far|fab|fal|fad)\s+fa-[\w-]+$/', $icon_value ) ) {
									printf( '<i class="%s" aria-hidden="true"></i>', esc_attr( $icon_value ) );
								} else {
									echo esc_html( $icon_value );
								}
							?></div>
							<span class="qeema-tech-stack__label"><?php echo esc_html( $item['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
