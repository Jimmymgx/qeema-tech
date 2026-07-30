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

	public function get_style_depends() {
		return array( 'qeema-hero-section' );
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
						<div class="qt-code-typing-widget" aria-hidden="true" data-qt-code="<?php echo esc_attr( $this->get_typing_code() ); ?>">
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
}
