<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reusable hero section — heading, subheading, optional buttons, optional
 * decorative visual. Ported from the "code typing" hero design used on the
 * old homepage; the visual variant control leaves room for the other hero
 * styles found across the site (corp/mobile pages) to be added later without
 * creating a new near-duplicate widget for each one.
 */
class Qeema_Hero_Section_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-hero-section';
	}

	public function get_title() {
		return __( 'Hero Section', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-slider-half-title';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-hero-section' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Content', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'نحوّل أفكارك إلى منتجات رقمية خارقة',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'نبني تطبيقات ومنصات عالية الأداء — من MVP إلى حلول مؤسسية.',
		) );

		$this->add_control( 'visual_variant', array(
			'label'   => __( 'Decorative Visual', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'code_typing',
			'options' => array(
				'none'        => __( 'None', 'qeematech-elementor-widgets' ),
				'code_typing' => __( 'Animated Code Typing', 'qeematech-elementor-widgets' ),
			),
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
					'default' => 'ابدأ مشروعك الآن',
				),
				array(
					'name'    => 'link',
					'label'   => __( 'Link', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::URL,
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
			'label'   => __( 'Background Color', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::COLOR,
		) );

		$this->add_control( 'background_image', array(
			'label'   => __( 'Background Image', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$style = '';
		if ( ! empty( $settings['background_color'] ) ) {
			$style .= 'background-color:' . esc_attr( $settings['background_color'] ) . ';';
		}
		if ( ! empty( $settings['background_image']['url'] ) ) {
			$style .= 'background-image:url(' . esc_url( $settings['background_image']['url'] ) . ');background-size:cover;background-position:center;';
		}

		$has_visual   = 'none' !== $settings['visual_variant'];
		$wrap_class   = 'qeema-hero-section__wrap' . ( $has_visual ? '' : ' qeema-hero--no-visual' );
		?>
		<section class="qeema-hero-section" <?php echo $style ? 'style="' . $style . '"' : ''; ?>>
			<div class="<?php echo esc_attr( $wrap_class ); ?>">
				<div class="qeema-hero-section__content">
					<h1><?php echo wp_kses_post( $settings['heading'] ); ?></h1>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $settings['buttons'] ) ) : ?>
						<div class="qeema-hero-section__actions" style="display:flex;gap:14px;flex-wrap:wrap;">
							<?php foreach ( $settings['buttons'] as $button ) : ?>
								<a class="qt-simple-btn <?php echo esc_attr( $button['style'] ); ?>"
									<?php echo ! empty( $button['link']['url'] ) ? 'href="' . esc_url( $button['link']['url'] ) . '"' : ''; ?>>
									<?php echo esc_html( $button['text'] ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( 'code_typing' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<div class="qt-code-typing-widget" style="direction:ltr;" aria-hidden="true" data-qt-code="<?php echo esc_attr( $this->get_typing_code() ); ?>">
							<?php echo $this->render_floating_icons(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
							<div class="qt-code-card">
								<div class="qt-code-top">
									<span class="qt-dot r"></span>
									<span class="qt-dot y"></span>
									<span class="qt-dot g"></span>
									<span class="qt-code-title">qeematech • live typing</span>
									<span class="qt-chip">LIVE</span>
								</div>
								<div class="qt-code-body">
									<div class="qt-code-glow"></div>
									<div class="qt-scanline"></div>
									<div class="qt-particles" aria-hidden="true"></div>
									<pre class="qt-pre"><code class="qt-typed-code"></code><span class="qt-caret"></span></pre>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	private function get_typing_code() {
		return "// QeemaTech – Platform Snippet\nconst brand = \"قيمة تك لتصميم وبرمجة المواقع\";\n\nfunction buildWebsite({ uiux, performance, seo }) {\n  return {\n    name: brand,\n    stack: [\"React\", \"Node.js\", \"WordPress\"],\n    uiux,\n    performance,\n    seo,\n    status: \"ready_to_launch\"\n  };\n}\n\nconst project = buildWebsite({\n  uiux: \"neon glass dark mode\",\n  performance: \"fast & optimized\",\n  seo: \"structured & scalable\"\n});\n\nconsole.log(project);";
	}

	/**
	 * Floating icon cluster around the code card. Each one carries its own
	 * icon + position + gradient class (fi-1..fi-6) so the CSS can give every
	 * icon a distinct float path/speed/delay instead of one repeated motion.
	 */
	private function render_floating_icons() {
		$icons = array(
			'fi-1' => '<polyline points="9 18 3 12 9 6"></polyline><polyline points="15 6 21 12 15 18"></polyline>',
			'fi-2' => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>',
			'fi-3' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>',
			'fi-4' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 12 15 16 10"></polyline>',
			'fi-5' => '<polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline>',
			'fi-6' => '<path d="M12 2l1.8 5.6L19 9l-5.2 1.4L12 16l-1.8-5.6L5 9l5.2-1.4L12 2z" fill="currentColor" stroke="none"></path>',
		);
		$out = '';
		foreach ( $icons as $class => $paths ) {
			$out .= '<div class="qt-ficon ' . esc_attr( $class ) . '"><svg class="qt-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg></div>';
		}
		return $out;
	}
}
