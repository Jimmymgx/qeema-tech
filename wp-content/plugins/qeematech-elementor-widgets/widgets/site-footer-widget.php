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

	public function get_script_depends() {
		return array( 'qeema-site-footer' );
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
			'label'   => __( 'Logo', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array(
				'url' => \Elementor\Utils::get_placeholder_image_src(),
			),
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
				// Human-readable platform name for the anchor's aria-label — the icon
				// class alone (e.g. "fab fa-facebook-f") isn't something a screen
				// reader should speak, so this exists purely for accessibility.
				array( 'name' => 'label', 'label' => 'Platform Name (accessibility label, e.g. Facebook)', 'type' => \Elementor\Controls_Manager::TEXT ),
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

	private function render_column( $column, $icon_class = '' ) {
		if ( empty( $column ) ) {
			return;
		}
		foreach ( $column as $col ) {
			if ( empty( $col['heading'] ) && empty( $col['links'] ) ) {
				continue;
			}
			?>
			<div class="qeema-footer__column">
				<?php if ( ! empty( $col['heading'] ) ) : ?>
					<h4>
						<?php if ( $icon_class ) : ?><?php qeema_fa_svg_e( $icon_class ); ?><?php endif; ?>
						<?php echo esc_html( $col['heading'] ); ?>
					</h4>
				<?php endif; ?>
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
			<img class="qeema-footer__watermark" src="<?php echo esc_url( trailingslashit( wp_upload_dir()['baseurl'] ) . '2026/08/qt-icon-only.png' ); ?>" alt="" aria-hidden="true">
			<div class="qeema-footer__wrap">
				<div class="qeema-footer__cards">
					<div class="qeema-footer__brand">
						<?php if ( ! empty( $settings['logo']['url'] ) && \Elementor\Utils::get_placeholder_image_src() !== $settings['logo']['url'] ) : ?>
							<?php if ( ! empty( $settings['logo']['id'] ) ) : ?>
								<?php echo wp_get_attachment_image( $settings['logo']['id'], 'medium', false, array( 'alt' => get_bloginfo( 'name' ) ) ); ?>
							<?php else : ?>
								<img src="<?php echo esc_url( $settings['logo']['url'] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
							<?php endif; ?>
						<?php endif; ?>
						<?php if ( ! empty( $settings['about_text'] ) ) : ?>
							<p><?php echo esc_html( $settings['about_text'] ); ?></p>
						<?php endif; ?>
						<div class="qeema-footer__stats">
							<div class="qeema-footer__stat"><strong>+6500</strong><span><?php esc_html_e( 'مشروع', 'qeematech-elementor-widgets' ); ?></span></div>
							<div class="qeema-footer__stat"><strong>+80</strong><span><?php esc_html_e( 'مهندس', 'qeematech-elementor-widgets' ); ?></span></div>
							<div class="qeema-footer__stat"><strong>99%</strong><span><?php esc_html_e( 'جاهزية', 'qeematech-elementor-widgets' ); ?></span></div>
						</div>
						<div class="qeema-footer__social">
							<?php foreach ( $settings['social_icons'] as $s ) : ?>
								<?php
								// Elementor pre-fills every repeater item with the control's
								// default ('') for fields not present when the item was first
								// saved, so `label` is always set but often empty — `??` alone
								// would never fall through, leaving a blank aria-label (still no
								// accessible name). Must check emptiness, not just isset().
								$social_label = ! empty( $s['label'] ) ? $s['label'] : 'رابط تواصل اجتماعي';
								?>
								<?php $social_svg = qeema_fa_svg( $s['icon_class'] ); ?>
								<a <?php echo ! empty( $s['link']['url'] ) ? 'href="' . esc_url( $s['link']['url'] ) . '"' : ''; ?> target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $social_label ); ?>"><?php
									if ( $social_svg ) {
										echo $social_svg; // phpcs:ignore WordPress.Security.EscapeOutput -- qeema_fa_svg() escapes internally.
									} else {
										// Editor typed a Font Awesome class this converted set doesn't
										// cover — fall back to the original icon-font rendering so
										// nothing breaks for icons outside the ~30 we converted.
										printf( '<i class="%s" aria-hidden="true"></i>', esc_attr( $s['icon_class'] ) );
									}
								?></a>
							<?php endforeach; ?>
						</div>
					</div>

					<?php
					$this->render_column( $settings['column1'], 'fas fa-link' );
					$this->render_column( $settings['column2'], 'fas fa-headset' );
					$this->render_column( $settings['column3'], 'fas fa-map-marker-alt' );
					?>
				</div>
			</div>

			<?php if ( ! empty( $settings['copyright_text'] ) ) : ?>
				<div class="qeema-footer__bottom">
					<span><?php echo esc_html( $settings['copyright_text'] ); ?></span>
					<a class="qeema-footer__totop" href="#" aria-label="<?php esc_attr_e( 'Back to top', 'qeematech-elementor-widgets' ); ?>">
						<?php qeema_fa_svg_e( 'fas fa-arrow-up' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</footer>
		<?php
	}
}
