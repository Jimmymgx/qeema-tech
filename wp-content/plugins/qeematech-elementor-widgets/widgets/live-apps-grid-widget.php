<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Full real-app catalog grid for the dedicated "/live-app/" page — one card
 * per real app (icon, name, real description, and a real Google Play / App
 * Store button each, whichever the app actually has). Unlike
 * app-store-proof-widget.php (a decorative 3-card fan for teaser sections)
 * this shows every app passed to it, so it's the widget for a full-catalog
 * listing rather than a highlight reel.
 */
class Qeema_Live_Apps_Grid_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-live-apps-grid';
	}

	public function get_title() {
		return __( 'Live Apps Grid', 'qeematech-elementor-widgets' );
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
			'default' => '',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'تطبيقاتنا على المتاجر',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'apps_section', array(
			'label' => __( 'Apps', 'qeematech-elementor-widgets' ),
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
					'name'    => 'description',
					'label'   => __( 'Description (real copy only)', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
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
	}

	private function render_card( $app ) {
		$google_url = $app['google_play_link']['url'] ?? '';
		$apple_url  = $app['apple_link']['url'] ?? '';
		if ( ! $google_url && ! $apple_url ) {
			return '';
		}
		$has_logo = ! empty( $app['logo']['url'] ) && \Elementor\Utils::get_placeholder_image_src() !== $app['logo']['url'];

		ob_start();
		?>
		<div class="qeema-live-apps-grid__card">
			<div class="qeema-live-apps-grid__card-inner">
				<span class="qeema-live-apps-grid__shine" aria-hidden="true"></span>
				<?php if ( $has_logo ) : ?>
					<div class="qeema-live-apps-grid__icon">
						<img src="<?php echo esc_url( $app['logo']['url'] ); ?>" alt="<?php echo esc_attr( $app['app_name'] ?? '' ); ?>" loading="lazy">
						<span class="qeema-live-apps-grid__live-dot" title="<?php esc_attr_e( 'متاح الآن على المتجر', 'qeematech-elementor-widgets' ); ?>"></span>
					</div>
				<?php endif; ?>
				<h3 class="qeema-live-apps-grid__name"><?php echo esc_html( $app['app_name'] ?? '' ); ?></h3>
				<?php if ( ! empty( $app['description'] ) ) : ?>
					<p class="qeema-live-apps-grid__desc"><?php echo esc_html( $app['description'] ); ?></p>
				<?php endif; ?>
				<div class="qeema-live-apps-grid__buttons">
					<?php if ( $google_url ) : ?>
						<a class="qeema-live-apps-grid__btn qeema-live-apps-grid__btn--play" href="<?php echo esc_url( $google_url ); ?>" target="_blank" rel="noopener noreferrer">
							<i class="fab fa-google-play" aria-hidden="true"></i><span><?php esc_html_e( 'Google Play', 'qeematech-elementor-widgets' ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $apple_url ) : ?>
						<a class="qeema-live-apps-grid__btn qeema-live-apps-grid__btn--apple" href="<?php echo esc_url( $apple_url ); ?>" target="_blank" rel="noopener noreferrer">
							<i class="fab fa-app-store-ios" aria-hidden="true"></i><span><?php esc_html_e( 'App Store', 'qeematech-elementor-widgets' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$cards    = array();
		foreach ( $settings['apps'] as $app ) {
			$card = $this->render_card( $app );
			if ( $card ) {
				$cards[] = $card;
			}
		}
		if ( ! $cards ) {
			return;
		}
		?>
		<section class="qeema-live-apps-grid">
			<div class="qeema-live-apps-grid__wrap">
				<?php if ( ! empty( $settings['heading'] ) ) : ?>
					<div class="qeema-live-apps-grid__head">
						<?php if ( ! empty( $settings['badge'] ) ) : ?>
							<span class="qeema-live-apps-grid__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
						<?php endif; ?>
						<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
						<?php if ( ! empty( $settings['subheading'] ) ) : ?>
							<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="qeema-live-apps-grid__list">
					<?php foreach ( $cards as $i => $card ) : ?>
						<div class="qeema-live-apps-grid__item" style="--i:<?php echo esc_attr( $i % 8 ); ?>"><?php echo $card; ?></div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
