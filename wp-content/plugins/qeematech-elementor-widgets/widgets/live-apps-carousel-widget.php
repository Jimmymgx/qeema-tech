<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Homepage "Live Apps" showcase — a 3D fanned stack of iPhone mockups, each
 * one styled like that app's real Google Play/App Store listing page (icon,
 * developer, install button, real description — no fabricated ratings, per
 * this project's established rule). Every phone is a real link to that
 * app's project case-study page; JS decides whether a click should just
 * bring a side phone to the front or actually navigate (see
 * assets/js/live-apps-carousel.js).
 */
class Qeema_Live_Apps_Carousel_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-live-apps-carousel';
	}

	public function get_title() {
		return __( 'Live Apps Carousel', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-carousel';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-live-apps-carousel' );
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
			'default' => 'تطبيقات حقيقية شغّالة على المتاجر دلوقتي',
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
					'name'    => 'name',
					'label'   => __( 'App name', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
				array(
					'name'    => 'developer',
					'label'   => __( 'Developer', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'قيمة تك',
				),
				array(
					'name'    => 'description',
					'label'   => __( 'Description (real copy only)', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
				),
				array(
					'name'    => 'store_type',
					'label'   => __( 'Store', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'play',
					'options' => array(
						'play'  => __( 'Google Play', 'qeematech-elementor-widgets' ),
						'apple' => __( 'App Store', 'qeematech-elementor-widgets' ),
					),
				),
				array(
					'name'    => 'link',
					'label'   => __( 'Link (project page or store link)', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::URL,
					'default' => array( 'url' => '' ),
				),
				array(
					'name'         => 'link_external',
					'label'        => __( 'Link leaves the site (opens in a new tab)', 'qeematech-elementor-widgets' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'default'      => '',
					'return_value' => 'yes',
				),
			),
			'default'     => array(),
			'title_field' => '{{{ name }}}',
		) );

		$this->end_controls_section();
	}

	private function render_phone( $app ) {
		$link        = $app['link']['url'] ?? '';
		$tag         = $link ? 'a' : 'div';
		$is_play     = 'apple' !== ( $app['store_type'] ?? 'play' );
		$dev_class   = $is_play ? 'qeema-live-apps-carousel__dev--play' : 'qeema-live-apps-carousel__dev--apple';
		$install_cls = $is_play ? 'qeema-live-apps-carousel__install--play' : 'qeema-live-apps-carousel__install--apple';
		$install_lbl = $is_play ? __( 'تثبيت', 'qeematech-elementor-widgets' ) : __( 'GET', 'qeematech-elementor-widgets' );

		ob_start();
		?>
		<div class="qeema-live-apps-carousel__phone">
			<<?php echo $tag; ?>
				class="qeema-live-apps-carousel__screen"
				<?php if ( $link ) : ?>
					href="<?php echo esc_url( $link ); ?>"
					<?php if ( 'yes' === ( $app['link_external'] ?? '' ) ) : ?>
						target="_blank" rel="noopener"
					<?php endif; ?>
				<?php endif; ?>
			>
				<div class="qeema-live-apps-carousel__statusbar">
					<span>9:41</span>
					<div class="qeema-live-apps-carousel__statusbar-icons">
						<div class="qeema-live-apps-carousel__sb-bar"><span></span><span></span><span></span><span></span></div>
						<div class="qeema-live-apps-carousel__sb-battery"></div>
					</div>
				</div>

				<div class="qeema-live-apps-carousel__storebar">
					<?php if ( $is_play ) : ?>
						<span class="qeema-live-apps-carousel__tri"></span><span>Google Play</span>
					<?php else : ?>
						<svg class="qeema-live-apps-carousel__store-icon" viewBox="0 0 24 24" fill="#111"><path d="M16.7 2c.1 1.2-.4 2.4-1.1 3.3-.7.9-1.9 1.6-3 1.5-.1-1.1.4-2.3 1.1-3.1C14.4 2.7 15.6 2.1 16.7 2zM20.7 17.3c-.5 1.1-.7 1.6-1.4 2.6-.9 1.4-2.2 3.1-3.8 3.1-1.4 0-1.8-.9-3.7-.9s-2.4.9-3.7.9c-1.6 0-2.8-1.5-3.7-2.9C2 17.4.9 13 2.4 10c.7-1.5 2-2.4 3.4-2.4 1.4 0 2.3.9 3.5.9 1.1 0 1.8-.9 3.7-.9 1.2 0 2.6.3 3.6 1.7-3.1 1.7-2.6 6.1.1 8z"/></svg><span>App Store</span>
					<?php endif; ?>
				</div>

				<div class="qeema-live-apps-carousel__apphead">
					<?php if ( ! empty( $app['logo']['url'] ) && \Elementor\Utils::get_placeholder_image_src() !== $app['logo']['url'] ) : ?>
						<div class="qeema-live-apps-carousel__apphead-icon"><img src="<?php echo esc_url( $app['logo']['url'] ); ?>" alt="" loading="lazy"></div>
					<?php endif; ?>
					<div class="qeema-live-apps-carousel__apphead-meta">
						<div class="qeema-live-apps-carousel__apphead-name"><?php echo esc_html( $app['name'] ?? '' ); ?></div>
						<?php if ( ! empty( $app['developer'] ) ) : ?>
							<div class="qeema-live-apps-carousel__dev <?php echo esc_attr( $dev_class ); ?>"><?php echo esc_html( $app['developer'] ); ?></div>
						<?php endif; ?>
					</div>
				</div>

				<div class="qeema-live-apps-carousel__install-row">
					<span class="qeema-live-apps-carousel__install <?php echo esc_attr( $install_cls ); ?>"><?php echo esc_html( $install_lbl ); ?></span>
				</div>

				<?php if ( ! empty( $app['description'] ) ) : ?>
					<div class="qeema-live-apps-carousel__about">
						<div class="qeema-live-apps-carousel__about-label"><?php esc_html_e( 'عن هذا التطبيق', 'qeematech-elementor-widgets' ); ?></div>
						<div class="qeema-live-apps-carousel__about-text"><?php echo esc_html( $app['description'] ); ?></div>
					</div>
				<?php endif; ?>
			</<?php echo $tag; ?>>
		</div>
		<?php
		return ob_get_clean();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$apps     = array_values( array_filter( $settings['apps'], function ( $app ) {
			return ! empty( $app['name'] );
		} ) );
		if ( ! $apps ) {
			return;
		}
		?>
		<section class="qeema-live-apps-carousel">
			<div class="qeema-live-apps-carousel__wrap">
				<div class="qeema-live-apps-carousel__head">
					<?php if ( ! empty( $settings['badge'] ) ) : ?>
						<span class="qeema-live-apps-carousel__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
					<?php endif; ?>
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="qeema-live-apps-carousel__stage-wrap">
					<div class="qeema-live-apps-carousel__glow"></div>
					<div class="qeema-live-apps-carousel__stage">
						<?php foreach ( $apps as $app ) : echo $this->render_phone( $app ); endforeach; ?>
					</div>
				</div>

				<div class="qeema-live-apps-carousel__nav">
					<button type="button" class="qeema-live-apps-carousel__next" aria-label="<?php esc_attr_e( 'التالي', 'qeematech-elementor-widgets' ); ?>">&lsaquo;</button>
					<button type="button" class="qeema-live-apps-carousel__prev" aria-label="<?php esc_attr_e( 'السابق', 'qeematech-elementor-widgets' ); ?>">&rsaquo;</button>
				</div>
				<div class="qeema-live-apps-carousel__dots">
					<?php foreach ( $apps as $app ) : ?>
						<span class="qeema-live-apps-carousel__dot"></span>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
