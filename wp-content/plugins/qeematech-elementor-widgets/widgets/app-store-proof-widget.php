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
			$app['store_icon']     = $google_url ? 'fab fa-google-play' : 'fab fa-app-store-ios';
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
									<img src="<?php echo esc_url( $app['logo']['url'] ); ?>" alt="<?php echo esc_attr( $app['app_name'] ); ?>" loading="lazy">
									<span class="qeema-app-store-proof__shine"></span>
								</span>
								<span class="qeema-app-store-proof__caption">
									<span class="qeema-app-store-proof__caption-badge store-<?php echo esc_attr( $app['store_platform'] ); ?>"><i class="<?php echo esc_attr( $app['store_icon'] ); ?>" aria-hidden="true"></i></span>
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
