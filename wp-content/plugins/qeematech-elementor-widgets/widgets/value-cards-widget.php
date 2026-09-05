<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mission/Vision/Goals-style card section for About-style pages — a bento
 * layout (first card featured, larger) with glow accent cards. Kept as its
 * own widget rather than extending `Qeema_Feature_Grid_Widget` (used by the
 * homepage's services grid) so that widget is never at risk of being
 * affected by About-page-specific changes.
 */
class Qeema_Value_Cards_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-value-cards';
	}

	public function get_title() {
		return __( 'Value Cards (bento)', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
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
			'default' => 'من نحن',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'نبني الثقة من خلال رؤية واضحة ورسالة قوية',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'cards_section', array(
			'label' => __( 'Cards (first card is the featured/large tile)', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'cards', array(
			'label'       => __( 'Cards', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'icon_text',
					'label'   => __( 'Icon (Font Awesome class, e.g. "fas fa-rocket")', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'fas fa-rocket',
				),
				array(
					'name'    => 'title',
					'label'   => __( 'Title', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'Card title',
				),
				array(
					'name'    => 'description',
					'label'   => __( 'Description', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
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
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="qeema-value-cards">
			<div class="qeema-value-cards__wrap">
				<div class="qeema-value-cards__head">
					<?php if ( ! empty( $settings['badge'] ) ) : ?>
						<span class="qeema-value-cards__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
					<?php endif; ?>
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="qeema-value-cards__grid">
					<?php foreach ( $settings['cards'] as $index => $card ) :
						$tag        = ! empty( $card['link']['url'] ) ? 'a' : 'div';
						$href       = ! empty( $card['link']['url'] ) ? ' href="' . esc_url( $card['link']['url'] ) . '"' : '';
						$card_class = 'qeema-value-card accent-' . ( ( $index % 3 ) + 1 ) . ' qeema-reveal';
						$delay      = esc_attr( $index * 0.12 );
						?>
						<<?php echo esc_attr( $tag ) . $href; ?> class="<?php echo esc_attr( $card_class ); ?>" style="--reveal-delay:<?php echo $delay; ?>s;">
							<div class="qeema-value-card__glow"></div>
							<div class="qeema-value-card__icon"><?php
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
					<div class="qeema-value-cards__actions">
						<?php foreach ( $settings['buttons'] as $button ) : ?>
							<a class="qeema-value-cards__btn <?php echo esc_attr( $button['style'] ); ?>"
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
