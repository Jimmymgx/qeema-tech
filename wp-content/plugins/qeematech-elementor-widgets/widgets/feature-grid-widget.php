<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reusable "badge + heading + subtext + icon-card grid" section. Consolidates
 * two near-identical hand-coded blocks found on production (the homepage
 * "Services" grid and the ODOO page's "Features" grid) into one widget.
 */
class Qeema_Feature_Grid_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-feature-grid';
	}

	public function get_title() {
		return __( 'Feature / Service Grid', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'header_section', array(
			'label' => __( 'Header', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'badge', array(
			'label'   => __( 'Badge', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'خدمات قيمة تك',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'حلول بسيطة… وتنفيذ احترافي',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'grid_section', array(
			'label' => __( 'Cards', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '4',
			'options' => array(
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
		) );

		$this->add_control( 'card_style', array(
			'label'   => __( 'Card Style', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'icons',
			'options' => array(
				'icons' => __( 'Icon Cards', 'qeematech-elementor-widgets' ),
				'glow'  => __( 'Glow Cards (mission/vision/values)', 'qeematech-elementor-widgets' ),
			),
		) );

		$this->add_control( 'cards', array(
			'label'       => __( 'Cards', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'icon_text',
					'label'   => __( 'Icon (emoji, glyph, or Font Awesome class e.g. "fas fa-laptop-code")', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '🔎',
				),
				array(
					'name'    => 'title',
					'label'   => __( 'Title', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'Service title',
				),
				array(
					'name'    => 'description',
					'label'   => __( 'Description', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
				),
				array(
					'name'  => 'link',
					'label' => __( 'Link', 'qeematech-elementor-widgets' ),
					'type'  => \Elementor\Controls_Manager::URL,
				),
			),
			'default'     => array(),
			'title_field' => '{{{ title }}}',
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
					'default' => 'Button',
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

		$this->start_controls_section( 'background_section', array(
			'label' => __( 'Background', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'background_color', array(
			'label' => __( 'Background Color', 'qeematech-elementor-widgets' ),
			'type'  => \Elementor\Controls_Manager::COLOR,
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$style      = ! empty( $settings['background_color'] )
			? 'background-color:' . esc_attr( $settings['background_color'] ) . ';'
			: '';
		$is_glow    = 'glow' === $settings['card_style'];
		$grid_class = 'qeema-feature-grid' . ( $is_glow ? ' qeema-feature-grid--glow' : '' );
		?>
		<section class="<?php echo esc_attr( $grid_class ); ?>" <?php echo $style ? 'style="' . $style . '"' : ''; ?>>
			<div class="qeema-feature-grid__wrap">
				<div class="qeema-feature-grid__head">
					<?php if ( ! empty( $settings['badge'] ) ) : ?>
						<span class="qeema-feature-grid__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
					<?php endif; ?>
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="qeema-feature-grid__grid" style="--qeema-grid-cols:<?php echo esc_attr( $settings['columns'] ); ?>;">
					<?php foreach ( $settings['cards'] as $index => $card ) :
						$tag        = ! empty( $card['link']['url'] ) ? 'a' : 'div';
						$href       = ! empty( $card['link']['url'] ) ? ' href="' . esc_url( $card['link']['url'] ) . '"' : '';
						$card_class = 'qeema-feature-card' . ( $is_glow ? ' accent-' . ( ( $index % 3 ) + 1 ) : '' );
						?>
						<<?php echo esc_attr( $tag ) . $href; ?> class="<?php echo esc_attr( $card_class ); ?>">
							<?php if ( $is_glow ) : ?>
								<div class="qeema-feature-card__glow"></div>
							<?php endif; ?>
							<div class="qeema-feature-card__icon"><?php
								$icon_value = trim( $card['icon_text'] );
								if ( preg_match( '/^(fas|far|fab|fal|fad)\s+fa-[\w-]+$/', $icon_value ) ) {
									printf( '<i class="%s" aria-hidden="true"></i>', esc_attr( $icon_value ) );
								} else {
									echo esc_html( $icon_value );
								}
							?></div>
							<h3><?php echo esc_html( $card['title'] ); ?></h3>
							<?php if ( ! empty( $card['description'] ) ) : ?>
								<p><?php echo esc_html( $card['description'] ); ?></p>
							<?php endif; ?>
						</<?php echo esc_attr( $tag ); ?>>
					<?php endforeach; ?>
				</div>

				<?php if ( ! empty( $settings['buttons'] ) ) : ?>
					<div class="qeema-feature-grid__actions">
						<?php foreach ( $settings['buttons'] as $button ) : ?>
							<a class="qeema-feature-grid__btn <?php echo esc_attr( $button['style'] ); ?>"
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
