<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Real-app proof showcase for mobile app development pages — a tilted 3D
 * "showcase fan" of real app screenshots (never a fabricated logo): a front
 * card facing the viewer with two supporting cards angled away in real
 * rotateY perspective, each with an idle float, a hover shine sweep, and a
 * floating name+store-platform caption pill (sized to its content, so long
 * app names never get clipped the way a fixed-width title bar would clip
 * them). Each card links out to whichever real store listing the app
 * actually has (Google Play preferred, App Store as fallback) — no
 * star-rating/download numbers are shown since those aren't tracked in the
 * CMS for these apps.
 */
class Qeema_App_Store_Proof_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-app-store-proof';
	}

	public function get_title() {
		return __( 'App Store Proof', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-mobile';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	/**
	 * Drives the 3-slot drag "merry-go-round" (see assets/js/app-store-proof.js)
	 * - continuously interpolates each card's translate/scale/tilt between the
	 * fixed a/b/c slot geometries defined in style.css instead of only
	 * snapping between them.
	 */
	public function get_script_depends() {
		return array( 'qeema-app-store-proof' );
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
			'default' => 'تطبيقات أطلقناها فعليًا',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'apps_section', array(
			'label' => __( 'Apps (first 3 are used)', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'apps', array(
			'label'       => __( 'Apps', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'logo',
					'label'   => __( 'Logo', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::MEDIA,
					'default' => array(
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					),
				),
				array(
					'name'    => 'app_name',
					'label'   => __( 'App Name', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'App Name',
				),
				array(
					'name'  => 'google_play_link',
					'label' => __( 'Google Play Link', 'qeematech-elementor-widgets' ),
					'type'  => \Elementor\Controls_Manager::URL,
				),
				array(
					'name'  => 'apple_link',
					'label' => __( 'App Store Link', 'qeematech-elementor-widgets' ),
					'type'  => \Elementor\Controls_Manager::URL,
				),
			),
			'default'     => array(),
			'title_field' => '{{{ app_name }}}',
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

	/**
	 * Inline SVG for the caption badge, swapped in for the fa-google-play /
	 * fa-app-store-ios icon-font glyphs. Path data is copied verbatim from
	 * Elementor's own bundled Font Awesome 5 Brands set (assets/lib/font-awesome/json/brands.json,
	 * icons "google-play" and "app-store-ios") so the shape stays pixel-accurate
	 * to what these cards showed before, instead of an invented replacement.
	 */
	private function store_icon_svg( $platform ) {
		$icons = array(
			'google' => '<svg class="qeema-app-store-proof__store-svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-14.3 18-46.5-1.2-60.8zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z"/></svg>',
			'apple'  => '<svg class="qeema-app-store-proof__store-svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zM127 384.5c-5.5 9.6-17.8 12.8-27.3 7.3-9.6-5.5-12.8-17.8-7.3-27.3l14.3-24.7c16.1-4.9 29.3-1.1 39.6 11.4L127 384.5zm138.9-53.9H84c-11 0-20-9-20-20s9-20 20-20h51l65.4-113.2-20.5-35.4c-5.5-9.6-2.2-21.8 7.3-27.3 9.6-5.5 21.8-2.2 27.3 7.3l8.9 15.4 8.9-15.4c5.5-9.6 17.8-12.8 27.3-7.3 9.6 5.5 12.8 17.8 7.3 27.3l-85.8 148.6h62.1c20.2 0 31.5 23.7 22.7 40zm98.1 0h-29l19.6 33.9c5.5 9.6 2.2 21.8-7.3 27.3-9.6 5.5-21.8 2.2-27.3-7.3-32.9-56.9-57.5-99.7-74-128.1-16.7-29-4.8-58 7.1-67.8 13.1 22.7 32.7 56.7 58.9 102h52c11 0 20 9 20 20 0 11.1-9 20-20 20z"/></svg>',
		);
		return $icons[ $platform ] ?? '';
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$slots    = array( 'a', 'b', 'c' );
		$apps     = array();
		foreach ( $settings['apps'] as $app ) {
			if ( empty( $app['logo']['url'] ) || \Elementor\Utils::get_placeholder_image_src() === $app['logo']['url'] ) {
				continue;
			}
			$google_url = $app['google_play_link']['url'] ?? '';
			$apple_url  = $app['apple_link']['url'] ?? '';
			if ( ! $google_url && ! $apple_url ) {
				continue;
			}
			$app['store_url']      = $google_url ? $google_url : $apple_url;
			$app['store_platform'] = $google_url ? 'google' : 'apple';
			$apps[]                = $app;
		}
		$apps  = array_slice( $apps, 0, 3 );
		$chips = array_slice( $settings['chips'], 0, 3 );
		?>
		<section class="qeema-app-store-proof">
			<div class="qeema-app-store-proof__wrap">
				<?php if ( ! empty( $settings['heading'] ) ) : ?>
					<div class="qeema-app-store-proof__head">
						<?php if ( ! empty( $settings['badge'] ) ) : ?>
							<span class="qeema-app-store-proof__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
						<?php endif; ?>
						<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
						<?php if ( ! empty( $settings['subheading'] ) ) : ?>
							<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="qeema-app-store-proof__stage">
					<div class="qeema-app-store-proof__halo"></div>
					<div class="qeema-app-store-proof__glow"></div>

					<?php foreach ( $apps as $index => $app ) :
						$slot = $slots[ $index ] ?? 'a';
						?>
						<a class="qeema-app-store-proof__card app-mock-<?php echo esc_attr( $slot ); ?>" href="<?php echo esc_url( $app['store_url'] ); ?>" target="_blank" rel="noopener noreferrer">
							<span class="qeema-app-store-proof__float">
								<span class="qeema-app-store-proof__art">
									<?php if ( ! empty( $app['logo']['id'] ) ) : ?>
										<?php echo wp_get_attachment_image( $app['logo']['id'], 'thumbnail', false, array( 'alt' => $app['app_name'] ) ); ?>
									<?php else : ?>
										<img src="<?php echo esc_url( $app['logo']['url'] ); ?>" alt="<?php echo esc_attr( $app['app_name'] ); ?>" loading="lazy">
									<?php endif; ?>
									<span class="qeema-app-store-proof__shine"></span>
								</span>
								<span class="qeema-app-store-proof__caption">
									<span class="qeema-app-store-proof__caption-badge store-<?php echo esc_attr( $app['store_platform'] ); ?>"><?php echo $this->store_icon_svg( $app['store_platform'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- static, hardcoded SVG markup, no user input. ?></span>
									<span class="qeema-app-store-proof__caption-name"><?php echo esc_html( $app['app_name'] ); ?></span>
								</span>
							</span>
						</a>
					<?php endforeach; ?>

					<?php foreach ( $chips as $index => $chip ) :
						$slot = $slots[ $index ] ?? 'a';
						if ( empty( $chip['text'] ) ) {
							continue;
						}
						?>
						<span class="qt-float-chip qeema-app-store-proof__chip qeema-app-store-proof__chip--<?php echo esc_attr( $slot ); ?>"><?php echo esc_html( $chip['text'] ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
