<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site-wide footer — logo/about, up to 3 link columns, social icons, and a
 * bottom copyright bar. Meant to be placed inside a Theme Builder footer
 * template, same role as production's Elementor Pro Theme Builder footer.
 */
class Qeema_Site_Footer_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-site-footer';
	}

	public function get_title() {
		return __( 'Site Footer', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-footer';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	private function link_column_fields() {
		return array(
			array( 'name' => 'heading', 'label' => 'Column Heading', 'type' => \Elementor\Controls_Manager::TEXT ),
			array(
				'name'   => 'links',
				'label'  => 'Links',
				'type'   => \Elementor\Controls_Manager::REPEATER,
				'fields' => array(
					array( 'name' => 'text', 'label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXT ),
					array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
				),
				'default' => array(),
				'title_field' => '{{{ text }}}',
			),
		);
	}

	protected function register_controls() {
		$this->start_controls_section( 'about_section', array(
			'label' => __( 'About Column', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'logo', array(
			'label' => __( 'Logo', 'qeematech-elementor-widgets' ),
			'type'  => \Elementor\Controls_Manager::MEDIA,
		) );
		$this->add_control( 'about_text', array(
			'label'   => __( 'About Text', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );
		$this->add_control( 'social_icons', array(
			'label'   => __( 'Social Icons', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => array(
				array( 'name' => 'icon_class', 'label' => 'Font Awesome class', 'type' => \Elementor\Controls_Manager::TEXT ),
				array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
			),
			'default' => array(),
			'title_field' => '{{{ icon_class }}}',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'column1_section', array(
			'label' => __( 'Link Column 1', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'column1', array(
			'label'  => __( 'Column 1', 'qeematech-elementor-widgets' ),
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $this->link_column_fields(),
			'default' => array(),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'column2_section', array(
			'label' => __( 'Link Column 2', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'column2', array(
			'label'  => __( 'Column 2', 'qeematech-elementor-widgets' ),
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $this->link_column_fields(),
			'default' => array(),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'column3_section', array(
			'label' => __( 'Link Column 3', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'column3', array(
			'label'  => __( 'Column 3', 'qeematech-elementor-widgets' ),
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $this->link_column_fields(),
			'default' => array(),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'bottom_section', array(
			'label' => __( 'Bottom Bar', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'copyright_text', array(
			'label'   => __( 'Copyright Text', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		) );
		$this->end_controls_section();
	}

	private function render_column( $column ) {
		if ( empty( $column ) ) {
			return;
		}
		foreach ( $column as $col ) {
			if ( empty( $col['heading'] ) && empty( $col['links'] ) ) {
				continue;
			}
			?>
			<div class="qeema-footer__column">
				<?php if ( ! empty( $col['heading'] ) ) : ?><h5><?php echo esc_html( $col['heading'] ); ?></h5><?php endif; ?>
				<ul class="qeema-footer__links">
					<?php foreach ( $col['links'] as $link ) : ?>
						<li><a <?php echo ! empty( $link['link']['url'] ) ? 'href="' . esc_url( $link['link']['url'] ) . '"' : ''; ?>><?php echo esc_html( $link['text'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		}
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<footer class="qeema-footer">
			<div class="qeema-footer__grid">
				<div class="qeema-footer__about">
					<?php if ( ! empty( $settings['logo']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $settings['logo']['url'] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<?php endif; ?>
					<?php if ( ! empty( $settings['about_text'] ) ) : ?>
						<p><?php echo esc_html( $settings['about_text'] ); ?></p>
					<?php endif; ?>
					<div class="qeema-footer__social">
						<?php foreach ( $settings['social_icons'] as $s ) : ?>
							<a <?php echo ! empty( $s['link']['url'] ) ? 'href="' . esc_url( $s['link']['url'] ) . '"' : ''; ?> target="_blank" rel="noopener"><i class="<?php echo esc_attr( $s['icon_class'] ); ?>"></i></a>
						<?php endforeach; ?>
					</div>
				</div>

				<?php
				$this->render_column( $settings['column1'] );
				$this->render_column( $settings['column2'] );
				$this->render_column( $settings['column3'] );
				?>
			</div>

			<?php if ( ! empty( $settings['copyright_text'] ) ) : ?>
				<div class="qeema-footer__bottom">
					<span><?php echo esc_html( $settings['copyright_text'] ); ?></span>
				</div>
			<?php endif; ?>
		</footer>
		<?php
	}
}
