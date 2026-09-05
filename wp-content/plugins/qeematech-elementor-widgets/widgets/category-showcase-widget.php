<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Icon-tile grid grounding an abstract service pitch in concrete scenarios
 * (e.g. "types of apps we build"). Kept generic/content-agnostic rather than
 * mobile-specific so later pages can reuse it for their own "kinds of X we
 * build" section without a near-duplicate widget.
 */
class Qeema_Category_Showcase_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-category-showcase';
	}

	public function get_title() {
		return __( 'Category Showcase', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
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
			'default' => 'أنواع نبنيها',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'items_section', array(
			'label' => __( 'Categories', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'items', array(
			'label'       => __( 'Items', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'icon_text',
					'label'   => __( 'Icon (Font Awesome class)', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'fas fa-star',
				),
				array(
					'name'    => 'title',
					'label'   => __( 'Title', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'Category',
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
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="qeema-category-showcase">
			<div class="qeema-category-showcase__wrap">
				<div class="qeema-category-showcase__head">
					<?php if ( ! empty( $settings['badge'] ) ) : ?>
						<span class="qeema-category-showcase__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
					<?php endif; ?>
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="qeema-category-showcase__grid">
					<?php foreach ( $settings['items'] as $index => $item ) :
						$delay        = esc_attr( $index * 0.08 );
						$accent_class = 'accent-' . ( ( $index % 3 ) + 1 );
						?>
						<div class="qeema-category-showcase__item <?php echo esc_attr( $accent_class ); ?> qeema-reveal" style="--reveal-delay:<?php echo $delay; ?>s;">
							<div class="qeema-category-showcase__icon"><i class="<?php echo esc_attr( $item['icon_text'] ); ?>" aria-hidden="true"></i></div>
							<h3 class="qeema-category-showcase__title"><?php echo esc_html( $item['title'] ); ?></h3>
							<?php if ( ! empty( $item['description'] ) ) : ?>
								<p class="qeema-category-showcase__desc"><?php echo esc_html( $item['description'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
