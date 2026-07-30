<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site-wide header — top contact bar, logo, nav (with dropdown support),
 * CTA button, and a mobile slide-out panel. Meant to be placed inside a
 * Theme Builder header template so it applies sitewide, same as production's
 * Elementor Pro Theme Builder header — but here it's one editable widget
 * instead of assembling loose native + ElementsKit widgets each time.
 */
class Qeema_Site_Header_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-site-header';
	}

	public function get_title() {
		return __( 'Site Header', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-header';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-site-header' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'logo_section', array(
			'label' => __( 'Logo', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'logo', array(
			'label' => __( 'Logo', 'qeematech-elementor-widgets' ),
			'type'  => \Elementor\Controls_Manager::MEDIA,
		) );
		$this->add_control( 'logo_link', array(
			'label'   => __( 'Logo Link', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => home_url( '/' ) ),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'topbar_section', array(
			'label' => __( 'Top Bar', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'show_topbar', array(
			'label'        => __( 'Show Top Bar', 'qeematech-elementor-widgets' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
		) );
		$this->add_control( 'topbar_contacts', array(
			'label'   => __( 'Contact Items', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => array(
				array( 'name' => 'icon', 'label' => 'Icon (dashicon class or emoji)', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '📞' ),
				array( 'name' => 'text', 'label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXT ),
				array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
			),
			'default' => array(
				array( 'icon' => '📞', 'text' => '01012804721', 'link' => array( 'url' => 'https://wa.me/201012804721' ) ),
				array( 'icon' => '✉', 'text' => 'Sales@qeematech.net', 'link' => array( 'url' => 'mailto:Sales@qeematech.net' ) ),
			),
			'title_field' => '{{{ text }}}',
		) );
		$this->add_control( 'topbar_social', array(
			'label'   => __( 'Social Icons', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => array(
				array( 'name' => 'icon_class', 'label' => 'Font Awesome class (e.g. fab fa-facebook)', 'type' => \Elementor\Controls_Manager::TEXT ),
				array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
			),
			'default' => array(
				array( 'icon_class' => 'fab fa-facebook', 'link' => array( 'url' => 'https://www.facebook.com/qeematechagency/' ) ),
				array( 'icon_class' => 'fab fa-x-twitter', 'link' => array( 'url' => 'https://x.com/TechQeema55' ) ),
				array( 'icon_class' => 'fab fa-youtube', 'link' => array( 'url' => 'https://www.youtube.com/@qeematech6099' ) ),
				array( 'icon_class' => 'fab fa-linkedin', 'link' => array( 'url' => 'https://www.linkedin.com/company/qeema-tech/' ) ),
			),
			'title_field' => '{{{ icon_class }}}',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'nav_section', array(
			'label' => __( 'Navigation', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'nav_items', array(
			'label'   => __( 'Menu Items', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => array(
				array( 'name' => 'label', 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Menu item' ),
				array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
				array(
					'name'    => 'children',
					'label'   => 'Dropdown Items',
					'type'    => \Elementor\Controls_Manager::REPEATER,
					'fields'  => array(
						array( 'name' => 'label', 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT ),
						array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
					),
					'default' => array(),
					'title_field' => '{{{ label }}}',
				),
			),
			'default'     => array(),
			'title_field' => '{{{ label }}}',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'cta_section', array(
			'label' => __( 'CTA Button', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'cta_text', array(
			'label'   => __( 'Text', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'طلب عرض سعر',
		) );
		$this->add_control( 'cta_link', array(
			'label' => __( 'Link', 'qeematech-elementor-widgets' ),
			'type'  => \Elementor\Controls_Manager::URL,
		) );
		$this->end_controls_section();
	}

	private function render_nav_items( $items, $is_mobile = false ) {
		foreach ( $items as $item ) {
			$has_children = ! empty( $item['children'] );
			$li_class     = $has_children ? ( $is_mobile ? '' : ' class="qeema-has-dropdown"' ) : '';
			echo '<li' . $li_class . '>';
			echo '<a' . ( ! empty( $item['link']['url'] ) ? ' href="' . esc_url( $item['link']['url'] ) . '"' : '' ) . '>' . esc_html( $item['label'] ) . '</a>';
			if ( $has_children ) {
				echo '<ul' . ( $is_mobile ? ' class="qeema-sub"' : '' ) . '>';
				foreach ( $item['children'] as $child ) {
					echo '<li><a' . ( ! empty( $child['link']['url'] ) ? ' href="' . esc_url( $child['link']['url'] ) . '"' : '' ) . '>' . esc_html( $child['label'] ) . '</a></li>';
				}
				echo '</ul>';
			}
			echo '</li>';
		}
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<header class="qeema-header">
			<?php if ( 'yes' === $settings['show_topbar'] ) : ?>
				<div class="qeema-header__topbar">
					<div class="qeema-header__topbar-contacts">
						<?php foreach ( $settings['topbar_contacts'] as $item ) : ?>
							<a <?php echo ! empty( $item['link']['url'] ) ? 'href="' . esc_url( $item['link']['url'] ) . '"' : ''; ?>>
								<span><?php echo esc_html( $item['icon'] ); ?></span> <?php echo esc_html( $item['text'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
					<div class="qeema-header__topbar-social">
						<?php foreach ( $settings['topbar_social'] as $s ) : ?>
							<a <?php echo ! empty( $s['link']['url'] ) ? 'href="' . esc_url( $s['link']['url'] ) . '"' : ''; ?> target="_blank" rel="noopener">
								<i class="<?php echo esc_attr( $s['icon_class'] ); ?>"></i>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="qeema-header__main">
				<a class="qeema-header__logo" href="<?php echo esc_url( $settings['logo_link']['url'] ?? home_url( '/' ) ); ?>">
					<?php if ( ! empty( $settings['logo']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $settings['logo']['url'] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<?php endif; ?>
				</a>

				<nav>
					<ul class="qeema-header__nav">
						<?php $this->render_nav_items( $settings['nav_items'] ); ?>
					</ul>
				</nav>

				<?php if ( ! empty( $settings['cta_text'] ) ) : ?>
					<a class="qeema-header__cta" <?php echo ! empty( $settings['cta_link']['url'] ) ? 'href="' . esc_url( $settings['cta_link']['url'] ) . '"' : ''; ?>>
						<?php echo esc_html( $settings['cta_text'] ); ?>
					</a>
				<?php endif; ?>

				<button class="qeema-header__toggle" aria-label="<?php esc_attr_e( 'Open menu', 'qeematech-elementor-widgets' ); ?>" aria-expanded="false">☰</button>
			</div>

			<div class="qeema-header__mobile-panel">
				<button class="qeema-header__mobile-close" aria-label="<?php esc_attr_e( 'Close menu', 'qeematech-elementor-widgets' ); ?>">×</button>
				<ul>
					<?php $this->render_nav_items( $settings['nav_items'], true ); ?>
				</ul>
			</div>
		</header>
		<?php
	}
}
