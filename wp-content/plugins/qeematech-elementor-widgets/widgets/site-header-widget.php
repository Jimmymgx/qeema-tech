<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site-wide header — logo, nav (with dropdown support), CTA button, and a
 * mobile slide-out panel. Single bar, matching production exactly (production
 * has no separate contact/social top bar — that content lives in the footer).
 * Meant to be placed inside a Theme Builder header template so it applies
 * sitewide, same as production's Elementor Pro Theme Builder header — but
 * here it's one editable widget instead of assembling loose native +
 * ElementsKit widgets each time.
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
		if ( ! $is_mobile ) {
			foreach ( $items as $item ) {
				$has_children = ! empty( $item['children'] );
				echo '<li' . ( $has_children ? ' class="qeema-has-dropdown"' : '' ) . '>';
				echo '<a' . ( ! empty( $item['link']['url'] ) ? ' href="' . esc_url( $item['link']['url'] ) . '"' : '' ) . '>' . esc_html( $item['label'] ) . '</a>';
				if ( $has_children ) {
					echo '<ul>';
					foreach ( $item['children'] as $child ) {
						echo '<li><a' . ( ! empty( $child['link']['url'] ) ? ' href="' . esc_url( $child['link']['url'] ) . '"' : '' ) . '>' . esc_html( $child['label'] ) . '</a></li>';
					}
					echo '</ul>';
				}
				echo '</li>';
			}
			return;
		}

		// Mobile: children collapse into a per-item accordion (toggle chevron
		// button, separate from the label link so the link still navigates),
		// and each row carries its own transition-delay so the panel's items
		// cascade in one after another instead of all snapping in at once.
		$index = 0;
		foreach ( $items as $item ) {
			$has_children = ! empty( $item['children'] );
			$delay        = round( $index * 0.05, 2 );
			echo '<li class="qeema-mobile-item' . ( $has_children ? ' qeema-has-children' : '' ) . '" style="transition-delay:' . esc_attr( $delay ) . 's">';
			echo '<div class="qeema-mobile-row">';
			echo '<a' . ( ! empty( $item['link']['url'] ) ? ' href="' . esc_url( $item['link']['url'] ) . '"' : '' ) . '>' . esc_html( $item['label'] ) . '</a>';
			if ( $has_children ) {
				echo '<button type="button" class="qeema-mobile-toggle" aria-label="' . esc_attr__( 'Toggle submenu', 'qeematech-elementor-widgets' ) . '" aria-expanded="false"><span class="qeema-mobile-chevron"></span></button>';
			}
			echo '</div>';
			if ( $has_children ) {
				echo '<ul class="qeema-sub">';
				foreach ( $item['children'] as $child ) {
					echo '<li><a' . ( ! empty( $child['link']['url'] ) ? ' href="' . esc_url( $child['link']['url'] ) . '"' : '' ) . '>' . esc_html( $child['label'] ) . '</a></li>';
				}
				echo '</ul>';
			}
			echo '</li>';
			$index++;
		}
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<header class="qeema-header">
			<div class="qeema-progress-bar"><div class="qeema-progress-bar__fill"></div></div>
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

				<button class="qeema-header__toggle" aria-label="<?php esc_attr_e( 'Open menu', 'qeematech-elementor-widgets' ); ?>" aria-expanded="false">
					<span class="qeema-burger"><span></span><span></span><span></span></span>
				</button>
			</div>

			<div class="qeema-header__overlay"></div>
			<div class="qeema-header__mobile-panel">
				<div class="qeema-header__mobile-top">
					<a class="qeema-header__logo" href="<?php echo esc_url( $settings['logo_link']['url'] ?? home_url( '/' ) ); ?>">
						<?php if ( ! empty( $settings['logo']['url'] ) ) : ?>
							<img src="<?php echo esc_url( $settings['logo']['url'] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<?php endif; ?>
					</a>
					<button class="qeema-header__mobile-close" aria-label="<?php esc_attr_e( 'Close menu', 'qeematech-elementor-widgets' ); ?>">
						<span class="qeema-x"><span></span><span></span></span>
					</button>
				</div>
				<ul class="qeema-mobile-nav">
					<?php $this->render_nav_items( $settings['nav_items'], true ); ?>
				</ul>
				<?php if ( ! empty( $settings['cta_text'] ) ) : ?>
					<a class="qeema-header__cta qeema-mobile-cta" <?php echo ! empty( $settings['cta_link']['url'] ) ? 'href="' . esc_url( $settings['cta_link']['url'] ) . '"' : ''; ?>>
						<?php echo esc_html( $settings['cta_text'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</header>
		<?php
	}
}
