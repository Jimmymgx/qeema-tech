<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site-wide header — logo, nav with rich mega-menu panels, CTA, mobile drawer.
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
			'label'   => __( 'Logo', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array(
				'url' => \Elementor\Utils::get_placeholder_image_src(),
			),
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
					'name'    => 'mega_eyebrow',
					'label'   => 'Mega Eyebrow',
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
				array(
					'name'    => 'mega_aside_title',
					'label'   => 'Mega Aside Title',
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
				array(
					'name'    => 'mega_aside_text',
					'label'   => 'Mega Aside Text',
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
				),
				array(
					'name'    => 'children',
					'label'   => 'Dropdown Items',
					'type'    => \Elementor\Controls_Manager::REPEATER,
					'fields'  => array(
						array( 'name' => 'label', 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT ),
						array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
						array( 'name' => 'description', 'label' => 'Description', 'type' => \Elementor\Controls_Manager::TEXTAREA ),
						array( 'name' => 'badge', 'label' => 'Badge', 'type' => \Elementor\Controls_Manager::TEXT ),
					),
					'default'     => array(),
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

	/**
	 * Enrich mega cards when Elementor children don't yet store descriptions.
	 */
	private function enrich_child( $child ) {
		$label = trim( (string) ( $child['label'] ?? '' ) );
		$desc  = trim( (string) ( $child['description'] ?? '' ) );
		$badge = trim( (string) ( $child['badge'] ?? '' ) );
		$icon  = 'spark';

		$presets = array(
			array( 'keys' => array( 'مواقع إلكترونية', 'تصميم المواقع', 'تصميم وتطوير المواقع' ), 'icon' => 'web', 'desc' => 'مواقع شركات بهوية قوية وتجربة استخدام سلسة.', 'badge' => 'Web' ),
			array( 'keys' => array( 'تطبيقات', 'موبايل', 'الهاتف' ), 'icon' => 'phone', 'desc' => 'تطبيقات iOS و Android بتجربة مستخدم واضحة.', 'badge' => 'Apps' ),
			array( 'keys' => array( 'متاجر', 'إلكترون' ), 'icon' => 'cart', 'desc' => 'متاجر جاهزة للبيع مع دفع وشحن وتقارير.', 'badge' => 'Store' ),
			array( 'keys' => array( 'استضافة' ), 'icon' => 'cloud', 'desc' => 'استضافة مستقرة وسريعة مع متابعة فنية.', 'badge' => 'Host' ),
			array( 'keys' => array( 'تسويق' ), 'icon' => 'megaphone', 'desc' => 'حملات رقمية تزيد الوصول والتحويل.', 'badge' => 'Ads' ),
			array( 'keys' => array( 'seo', 'محركات', 'تهيئة' ), 'icon' => 'search', 'desc' => 'ظهور أقوى في نتائج البحث وزيادة الزيارات.', 'badge' => 'SEO' ),
			array( 'keys' => array( 'برمجة خاصة', 'مشاريع برمجة' ), 'icon' => 'code', 'desc' => 'حلول مخصصة حسب احتياج نشاطك.', 'badge' => 'Custom' ),
			array( 'keys' => array( 'crm' ), 'icon' => 'users', 'desc' => 'إدارة العملاء والمبيعات في منصة واحدة.', 'badge' => 'CRM' ),
			array( 'keys' => array( 'odoo', 'erp' ), 'icon' => 'layers', 'desc' => 'تخطيط موارد المؤسسة وتكامل العمليات.', 'badge' => 'ERP' ),
			array( 'keys' => array( 'شركات' ), 'icon' => 'building', 'desc' => 'مواقع شركات تعكس الثقة والاحتراف.', 'badge' => 'Biz' ),
			array( 'keys' => array( 'تعليم' ), 'icon' => 'book', 'desc' => 'منصات تعليمية ودورات بتجربة سلسة.', 'badge' => 'Edu' ),
			array( 'keys' => array( 'سياح' ), 'icon' => 'map', 'desc' => 'مواقع سياحية للحجز والعروض.', 'badge' => 'Travel' ),
			array( 'keys' => array( 'live app', 'live' ), 'icon' => 'bolt', 'desc' => 'تطبيقات حية جاهزة للتجربة الآن.', 'badge' => 'Live' ),
		);

		foreach ( $presets as $preset ) {
			foreach ( $preset['keys'] as $key ) {
				if ( false !== mb_stripos( $label, $key ) ) {
					$icon = $preset['icon'];
					if ( '' === $desc ) {
						$desc = $preset['desc'];
					}
					if ( '' === $badge ) {
						$badge = $preset['badge'];
					}
					break 2;
				}
			}
		}

		if ( '' === $desc ) {
			$desc = 'تعرّف على التفاصيل وابدأ مشروعك مع فريق قيمة تك.';
		}

		$child['description'] = $desc;
		$child['badge']       = $badge;
		$child['icon']        = $icon;
		return $child;
	}

	private function icon_svg( $type ) {
		$icons = array(
			'web'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 20h8"/></svg>',
			'phone'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg>',
			'cart'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 5h2l2.4 11h9.8L21 8H7"/><circle cx="9" cy="20" r="1.4"/><circle cx="17" cy="20" r="1.4"/></svg>',
			'cloud'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 18h10a4 4 0 0 0 .3-8 5.5 5.5 0 0 0-10.6 1.5A3.5 3.5 0 0 0 7 18z"/></svg>',
			'megaphone'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 11v2a2 2 0 0 0 2 2h2l6 4V5L7 9H5a2 2 0 0 0-2 2z"/><path d="M15 9.5c1.2.6 2 1.5 2 2.5s-.8 1.9-2 2.5"/></svg>',
			'search'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="6"/><path d="m20 20-3.5-3.5"/></svg>',
			'code'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 4l-4 16"/></svg>',
			'users'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M3 19c0-3 2.5-5 6-5s6 2 6 5"/><circle cx="17" cy="9" r="2.2"/><path d="M21 19c0-2.2-1.5-3.8-4-4.2"/></svg>',
			'layers'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m12 3 9 5-9 5-9-5 9-5z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/></svg>',
			'building' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20V6l8-3 8 3v14"/><path d="M9 20v-6h6v6M9 9h.01M15 9h.01M9 13h.01M15 13h.01"/></svg>',
			'book'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 4h11a3 3 0 0 1 3 3v13H8a3 3 0 0 0-3 3V4z"/><path d="M8 4v16"/></svg>',
			'map'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m9 4-6 2v14l6-2 6 2 6-2V2l-6 2-6-2z"/><path d="M9 4v14M15 6v14"/></svg>',
			'bolt'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M13 2 4 14h7l-1 8 10-14h-7l0-6z"/></svg>',
			'spark'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v4M12 17v4M4.9 6.5l2.8 2.8M16.3 14.7l2.8 2.8M3 12h4M17 12h4M4.9 17.5l2.8-2.8M16.3 9.3l2.8-2.8"/></svg>',
		);
		return $icons[ $type ] ?? $icons['spark'];
	}

	private function render_mega_panel( $item, $cta_text, $cta_link ) {
		$children = array_map( array( $this, 'enrich_child' ), $item['children'] );
		$label    = (string) ( $item['label'] ?? '' );
		$eyebrow  = trim( (string) ( $item['mega_eyebrow'] ?? '' ) );
		$aside_t  = trim( (string) ( $item['mega_aside_title'] ?? '' ) );
		$aside_p  = trim( (string) ( $item['mega_aside_text'] ?? '' ) );
		$parent   = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '';

		if ( '' === $eyebrow ) {
			$eyebrow = ( false !== mb_strpos( $label, 'أعمال' ) )
				? 'معرض المشاريع حسب التصنيف'
				: 'حلول رقمية متكاملة من قيمة تك';
		}
		if ( '' === $aside_t ) {
			$aside_t = ( false !== mb_strpos( $label, 'أعمال' ) )
				? 'شاهد نتائج حقيقية'
				: 'جاهز تبدأ مشروعك؟';
		}
		if ( '' === $aside_p ) {
			$aside_p = ( false !== mb_strpos( $label, 'أعمال' ) )
				? 'تصفّح دراسات الحالة والتطبيقات والمواقع التي أنجزناها لعملائنا.'
				: 'مستشارك التقني جاهز لتحويل فكرتك إلى منتج رقمي قابل للنمو.';
		}

		$cols = count( $children ) >= 8 ? ' is-dense' : '';
		?>
		<div class="qeema-mega<?php echo esc_attr( $cols ); ?>" role="region" aria-label="<?php echo esc_attr( $label ); ?>">
			<div class="qeema-mega__panel">
				<div class="qeema-mega__main">
					<p class="qeema-mega__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
					<div class="qeema-mega__grid">
						<?php foreach ( $children as $child ) : ?>
							<a class="qeema-mega__card" <?php echo ! empty( $child['link']['url'] ) ? 'href="' . esc_url( $child['link']['url'] ) . '"' : ''; ?>>
								<span class="qeema-mega__icon" aria-hidden="true"><?php echo $this->icon_svg( $child['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="qeema-mega__copy">
									<span class="qeema-mega__title-row">
										<strong class="qeema-mega__title"><?php echo esc_html( $child['label'] ); ?></strong>
										<?php if ( ! empty( $child['badge'] ) ) : ?>
											<span class="qeema-mega__badge"><?php echo esc_html( $child['badge'] ); ?></span>
										<?php endif; ?>
									</span>
									<span class="qeema-mega__desc"><?php echo esc_html( $child['description'] ); ?></span>
								</span>
								<span class="qeema-mega__go" aria-hidden="true">←</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
				<aside class="qeema-mega__aside">
					<span class="qeema-mega__aside-kicker">قيمة تك</span>
					<h4><?php echo esc_html( $aside_t ); ?></h4>
					<p><?php echo esc_html( $aside_p ); ?></p>
					<?php if ( ! empty( $cta_text ) ) : ?>
						<a class="qeema-mega__aside-btn" <?php echo ! empty( $cta_link ) ? 'href="' . esc_url( $cta_link ) . '"' : ''; ?>>
							<?php echo esc_html( $cta_text ); ?>
						</a>
					<?php endif; ?>
					<ul class="qeema-mega__aside-meta">
						<li><strong>+50</strong><span>مشروع منجز</span></li>
						<li><strong>10+</strong><span>سنوات خبرة</span></li>
					</ul>
				</aside>
			</div>
			<div class="qeema-mega__foot">
				<span>تصميم • تطوير • إطلاق • دعم مستمر</span>
				<?php if ( $parent ) : ?>
					<a href="<?php echo esc_url( $parent ); ?>">عرض كل <?php echo esc_html( $label ); ?> ←</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	private function render_nav_items( $items, $is_mobile = false, $cta_text = '', $cta_link = '' ) {
		if ( ! $is_mobile ) {
			foreach ( $items as $item ) {
				$has_children = ! empty( $item['children'] );
				$is_mega      = $has_children && count( $item['children'] ) >= 4;
				$li_class     = array();
				if ( $has_children ) {
					$li_class[] = 'qeema-has-dropdown';
				}
				if ( $is_mega ) {
					$li_class[] = 'qeema-has-mega';
				}
				echo '<li' . ( $li_class ? ' class="' . esc_attr( implode( ' ', $li_class ) ) . '"' : '' ) . '>';
				echo '<a' . ( ! empty( $item['link']['url'] ) ? ' href="' . esc_url( $item['link']['url'] ) . '"' : '' ) . '>' . esc_html( $item['label'] );
				if ( $has_children ) {
					echo '<span class="qeema-nav-caret" aria-hidden="true"></span>';
				}
				echo '</a>';
				if ( $is_mega ) {
					$this->render_mega_panel( $item, $cta_text, $cta_link );
				} elseif ( $has_children ) {
					echo '<ul class="qeema-mini-drop">';
					foreach ( $item['children'] as $child ) {
						$child = $this->enrich_child( $child );
						echo '<li><a' . ( ! empty( $child['link']['url'] ) ? ' href="' . esc_url( $child['link']['url'] ) . '"' : '' ) . '>';
						echo '<span class="qeema-mini-drop__icon" aria-hidden="true">' . $this->icon_svg( $child['icon'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo '<span class="qeema-mini-drop__copy"><strong>' . esc_html( $child['label'] ) . '</strong>';
						if ( ! empty( $child['description'] ) ) {
							echo '<em>' . esc_html( $child['description'] ) . '</em>';
						}
						echo '</span></a></li>';
					}
					echo '</ul>';
				}
				echo '</li>';
			}
			return;
		}

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
					$child = $this->enrich_child( $child );
					echo '<li><a' . ( ! empty( $child['link']['url'] ) ? ' href="' . esc_url( $child['link']['url'] ) . '"' : '' ) . '>';
					echo '<span class="qeema-sub__icon" aria-hidden="true">' . $this->icon_svg( $child['icon'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<span class="qeema-sub__copy"><strong>' . esc_html( $child['label'] ) . '</strong>';
					if ( ! empty( $child['description'] ) ) {
						echo '<em>' . esc_html( $child['description'] ) . '</em>';
					}
					echo '</span></a></li>';
				}
				echo '</ul>';
			}
			echo '</li>';
			$index++;
		}
	}

	protected function render() {
		$settings        = $this->get_settings_for_display();
		$logo_mobile_url = trailingslashit( wp_upload_dir()['baseurl'] ) . '2026/08/qt-icon-only.png';
		$cta_text        = $settings['cta_text'] ?? '';
		$cta_link        = $settings['cta_link']['url'] ?? '';
		?>
		<header class="qeema-header">
			<div class="qeema-progress-bar"><div class="qeema-progress-bar__fill"></div></div>
			<div class="qeema-header__main">
				<a class="qeema-header__logo" href="<?php echo esc_url( $settings['logo_link']['url'] ?? home_url( '/' ) ); ?>">
					<?php if ( ! empty( $settings['logo']['url'] ) && \Elementor\Utils::get_placeholder_image_src() !== $settings['logo']['url'] ) : ?>
						<img class="qeema-header__logo-full" src="<?php echo esc_url( $settings['logo']['url'] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<img class="qeema-header__logo-mobile" src="<?php echo esc_url( $logo_mobile_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<?php endif; ?>
				</a>

				<nav>
					<ul class="qeema-header__nav">
						<?php $this->render_nav_items( $settings['nav_items'], false, $cta_text, $cta_link ); ?>
					</ul>
				</nav>

				<?php if ( ! empty( $cta_text ) ) : ?>
					<a class="qeema-header__cta" <?php echo $cta_link ? 'href="' . esc_url( $cta_link ) . '"' : ''; ?>>
						<?php echo esc_html( $cta_text ); ?>
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
						<?php if ( ! empty( $settings['logo']['url'] ) && \Elementor\Utils::get_placeholder_image_src() !== $settings['logo']['url'] ) : ?>
							<img class="qeema-header__logo-full" src="<?php echo esc_url( $settings['logo']['url'] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
							<img class="qeema-header__logo-mobile" src="<?php echo esc_url( $logo_mobile_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<?php endif; ?>
					</a>
					<button class="qeema-header__mobile-close" aria-label="<?php esc_attr_e( 'Close menu', 'qeematech-elementor-widgets' ); ?>">
						<span class="qeema-x"><span></span><span></span></span>
					</button>
				</div>
				<ul class="qeema-mobile-nav">
					<?php $this->render_nav_items( $settings['nav_items'], true, $cta_text, $cta_link ); ?>
				</ul>
				<?php if ( ! empty( $cta_text ) ) : ?>
					<a class="qeema-header__cta qeema-mobile-cta" <?php echo $cta_link ? 'href="' . esc_url( $cta_link ) . '"' : ''; ?>>
						<?php echo esc_html( $cta_text ); ?>
					</a>
				<?php endif; ?>
			</div>
		</header>
		<?php
	}
}
