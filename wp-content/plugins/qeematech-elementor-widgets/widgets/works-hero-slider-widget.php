<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * أعمالنا page hero — an auto-playing multi-slide banner (one slide per
 * portfolio category), matching the old site's real hero structure. Visuals
 * deliberately reuse this codebase's already-proven, purely decorative
 * mockup recipes (about-hero-widget.php's static `phone_mockups`,
 * browser-showcase-widget.php's browser-chrome frame) instead of new
 * illustrations, so no new image assets are needed — every visual here is
 * CSS/HTML + Font Awesome icons only, same as those two widgets.
 */
class Qeema_Works_Hero_Slider_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-works-hero-slider';
	}

	public function get_title() {
		return __( 'Works Hero Slider', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-works-hero-slider' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'slides_section', array(
			'label' => __( 'Slides', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'slides', array(
			'label'       => __( 'Slides', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'badge',
					'label'   => __( 'Category Badge', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
				array(
					'name'    => 'heading',
					'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
				array(
					'name'    => 'description',
					'label'   => __( 'Description', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
				),
				array(
					'name'    => 'visual_variant',
					'label'   => __( 'Visual', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'icon',
					'options' => array(
						'browser' => __( 'Browser Mockup', 'qeematech-elementor-widgets' ),
						'phone'   => __( 'Phone Mockups', 'qeematech-elementor-widgets' ),
						'icon'    => __( 'Icon Badge', 'qeematech-elementor-widgets' ),
						'facets'  => __( 'Store Screens (App Logos)', 'qeematech-elementor-widgets' ),
					),
				),
				array(
					'name'    => 'icon',
					'label'   => __( 'Icon Class (used when Visual = Icon Badge)', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'fas fa-code',
					'condition' => array( 'visual_variant' => array( 'icon', '' ) ),
				),
				array(
					'name'        => 'facet_apps',
					'label'       => __( 'Store Apps (used when Visual = Store Screens)', 'qeematech-elementor-widgets' ),
					'type'        => \Elementor\Controls_Manager::REPEATER,
					'condition'   => array( 'visual_variant' => 'facets' ),
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
							'label'   => __( 'App Name', 'qeematech-elementor-widgets' ),
							'type'    => \Elementor\Controls_Manager::TEXT,
							'default' => '',
						),
						array(
							'name'    => 'description',
							'label'   => __( 'Short Description (real copy only, no fabricated ratings)', 'qeematech-elementor-widgets' ),
							'type'    => \Elementor\Controls_Manager::TEXT,
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
					'title_field' => '{{{ name }}}',
				),
				array(
					'name'    => 'cta_text',
					'label'   => __( 'Primary Button Text', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
				array(
					'name'  => 'cta_link',
					'label' => __( 'Primary Button Link', 'qeematech-elementor-widgets' ),
					'type'  => \Elementor\Controls_Manager::URL,
				),
			),
			'default'     => array(),
			'title_field' => '{{{ heading }}}',
		) );

		$this->add_control( 'secondary_cta_text', array(
			'label'   => __( 'Secondary Button Text (links to the grid below)', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'شاهد أعمالنا',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$slides   = ! empty( $settings['slides'] ) ? $settings['slides'] : array();
		if ( ! $slides ) {
			return;
		}
		?>
		<section class="qeema-works-hero<?php echo 1 === count( $slides ) ? ' qeema-works-hero--single' : ''; ?>">
			<div class="qeema-works-hero__stage">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<div class="qeema-works-hero__slide<?php echo 0 === $index ? ' is-active' : ''; ?>">
						<div class="qeema-works-hero__content">
							<?php if ( ! empty( $slide['badge'] ) ) : ?>
								<span class="qeema-works-hero__badge"><?php echo esc_html( $slide['badge'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $slide['heading'] ) ) : ?>
								<h2 class="qeema-works-hero__heading"><?php echo esc_html( $slide['heading'] ); ?></h2>
							<?php endif; ?>
							<?php if ( ! empty( $slide['description'] ) ) : ?>
								<p class="qeema-works-hero__description"><?php echo esc_html( $slide['description'] ); ?></p>
							<?php endif; ?>
							<div class="qeema-works-hero__ctas">
								<?php if ( ! empty( $slide['cta_text'] ) ) : ?>
									<a class="qt-simple-btn primary" <?php echo ! empty( $slide['cta_link']['url'] ) ? 'href="' . esc_url( $slide['cta_link']['url'] ) . '"' : ''; ?>>
										<?php echo esc_html( $slide['cta_text'] ); ?>
									</a>
								<?php endif; ?>
								<?php if ( ! empty( $settings['secondary_cta_text'] ) ) : ?>
									<a class="qt-simple-btn ghost" href="#portfolio">
										<?php echo esc_html( $settings['secondary_cta_text'] ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
						<div class="qeema-works-hero__visual">
							<?php
							$variant = ! empty( $slide['visual_variant'] ) ? $slide['visual_variant'] : 'icon';
							if ( 'browser' === $variant ) {
								echo $this->render_browser_visual(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup
							} elseif ( 'phone' === $variant ) {
								echo $this->render_phone_visual(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup
							} elseif ( 'facets' === $variant ) {
								echo $this->render_facet_visual( ! empty( $slide['facet_apps'] ) ? $slide['facet_apps'] : array() ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped internally
							} else {
								echo $this->render_icon_visual( ! empty( $slide['icon'] ) ? $slide['icon'] : 'fas fa-code', ! empty( $slide['badge'] ) ? $slide['badge'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped internally
							}
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( count( $slides ) > 1 ) : ?>
				<div class="qeema-works-hero__dots">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<button type="button" class="qeema-works-hero__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'Slide %d', 'qeematech-elementor-widgets' ), $index + 1 ) ); ?>"></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Reuses `.qt-phone-mock`'s shape classes verbatim from
	 * about-hero-widget.php's render_phone_mockups() (those set no
	 * position of their own, so they're safe as-is), but does NOT reuse the
	 * bare `.phone-a/b/c`/`.chip-a/b/c` position classes — those ARE bare,
	 * percentage-positioned globals tuned for `.qeema-hero-section__visual`'s
	 * specific box, and this widget's own `browser-showcase.css` history
	 * already shows what happens when a differently-sized container reuses a
	 * bare positioned slot class (a stray leaked property inflated a chip to
	 * 441px tall). New `qeema-works-hero__*` position classes avoid that,
	 * exactly like `browser-showcase.css` did for its own chips.
	 */
	private function render_phone_visual() {
		return '
			<div class="qt-phones-glow"></div>
			<div class="qt-phone-mock qeema-works-hero__phone--a">
				<div class="qt-phone-mock__notch"></div>
				<div class="qt-phone-mock__screen">
					<div class="qt-phone-mock__bar"></div>
					<div class="qt-phone-mock__card"></div>
					<div class="qt-phone-mock__card small"></div>
				</div>
			</div>
			<div class="qt-phone-mock qeema-works-hero__phone--b">
				<div class="qt-phone-mock__notch"></div>
				<div class="qt-phone-mock__screen alt">
					<div class="qt-phone-mock__bar"></div>
					<div class="qt-phone-mock__card"></div>
					<div class="qt-phone-mock__card small"></div>
				</div>
			</div>
			<div class="qt-phone-mock qeema-works-hero__phone--c">
				<div class="qt-phone-mock__notch"></div>
				<div class="qt-phone-mock__screen alt2">
					<div class="qt-phone-mock__bar"></div>
					<div class="qt-phone-mock__card"></div>
					<div class="qt-phone-mock__card small"></div>
				</div>
			</div>
			<div class="qt-float-chip qeema-works-hero__chip--a">تطبيقات iOS</div>
			<div class="qt-float-chip qeema-works-hero__chip--b">تطبيقات Android</div>
			<div class="qt-float-chip qeema-works-hero__chip--c">تجربة سلسة</div>
		';
	}

	/**
	 * Adapts browser-showcase-widget.php's `.qeema-browser-mock` chrome (bar +
	 * traffic-light dots + fake URL label), matching phone_mockups' own
	 * no-real-image precedent (a decorative skeleton, no uploaded screenshot
	 * required) — but instead of leaving the screen a plain empty gradient,
	 * it animates a small "company site building itself" sequence purely in
	 * CSS: a nav bar and hero copy draw themselves in, a cursor arrives and
	 * clicks the CTA, then a feature row reveals — looping. Uses its own
	 * `qeema-works-hero__browser` position class rather than the bare
	 * `.browser-a` (same bare-slot-class leak risk as the phone visual above).
	 */
	private function render_browser_visual() {
		return '
			<div class="qt-services-glow"></div>
			<div class="qeema-browser-mock qeema-works-hero__browser">
				<div class="qeema-browser-mock__bar">
					<span class="qt-dot r"></span>
					<span class="qt-dot y"></span>
					<span class="qt-dot g"></span>
					<span class="qeema-browser-mock__url">qeematech.net</span>
				</div>
				<div class="qeema-browser-mock__screen qeema-works-hero__browser-screen qeema-site-build">
					<div class="qeema-site-build__nav">
						<span class="qeema-site-build__logo-dot"></span>
						<span class="qeema-site-build__nav-link l1"></span>
						<span class="qeema-site-build__nav-link l2"></span>
						<span class="qeema-site-build__nav-link l3"></span>
					</div>
					<div class="qeema-site-build__hero">
						<span class="qeema-site-build__bar big"></span>
						<span class="qeema-site-build__bar big w2"></span>
						<span class="qeema-site-build__bar small"></span>
						<span class="qeema-site-build__cta"></span>
					</div>
					<div class="qeema-site-build__features">
						<span class="qeema-site-build__feature"><i></i></span>
						<span class="qeema-site-build__feature"><i></i></span>
						<span class="qeema-site-build__feature"><i></i></span>
					</div>
					<div class="qeema-site-build__cursor"></div>
				</div>
			</div>
			<div class="qt-float-chip qeema-works-hero__chip--a">تصميم عصري</div>
			<div class="qt-float-chip qeema-works-hero__chip--b">أداء سريع</div>
		';
	}

	/**
	 * "Store Screens" — two phone mockups styled after the real Google Play
	 * and App Store browsing screens (status bar, search/header, category
	 * tabs, an auto-scrolling app-row list, bottom tab bar), populated with
	 * real apps. Each row's subtitle is that app's own real `description`
	 * ACF value (truncated), never a fabricated rating/category — matching
	 * app-store-proof-widget.php's own "no fabricated star-rating/download
	 * numbers" rule. Install/GET buttons link to each app's real store URL
	 * when set. First half of $apps renders on the Play Store phone, the
	 * rest on the App Store phone (which also uses its own first two apps
	 * for the crossfading "app of the day" feature card).
	 */
	private function render_facet_visual( $apps ) {
		$apps = array_values( array_filter( $apps, function( $app ) {
			return ! empty( $app['logo']['url'] ) && \Elementor\Utils::get_placeholder_image_src() !== $app['logo']['url'];
		} ) );
		if ( ! $apps ) {
			return '<div class="qeema-store-screens__glow"></div>';
		}

		$half        = (int) ceil( count( $apps ) / 2 );
		$play_apps   = array_slice( $apps, 0, $half );
		$apple_apps  = array_slice( $apps, $half );
		if ( ! $apple_apps ) {
			$apple_apps = $play_apps;
		}

		$render_row = function( $app, $is_apple ) {
			$link  = $is_apple ? ( $app['apple_link']['url'] ?? '' ) : ( $app['google_play_link']['url'] ?? '' );
			$tag   = $link ? 'a' : 'div';
			$href  = $link ? ' href="' . esc_url( $link ) . '" target="_blank" rel="noopener"' : '';
			$label = $is_apple ? 'احصل عليه' : 'تثبيت';
			return '<' . $tag . ' class="qeema-store-row"' . $href . '>'
				. '<div class="qeema-store-row__plate"><img src="' . esc_url( $app['logo']['url'] ) . '" alt="" loading="lazy"></div>'
				. '<div class="qeema-store-row__meta"><div class="qeema-store-row__name">' . esc_html( $app['name'] ) . '</div>'
				. ( ! empty( $app['description'] ) ? '<div class="qeema-store-row__sub">' . esc_html( $app['description'] ) . '</div>' : '' )
				. '</div>'
				. '<span class="qeema-store-row__btn">' . esc_html( $label ) . '</span>'
				. '</' . $tag . '>';
		};

		$render_track = function( $row_apps, $is_apple ) use ( $render_row ) {
			// Duplicated once so the CSS scroll loop (translateY 0 → -50%) is seamless.
			$rows = '';
			foreach ( array_merge( $row_apps, $row_apps ) as $app ) {
				$rows .= $render_row( $app, $is_apple );
			}
			return $rows;
		};

		ob_start();
		?>
		<div class="qeema-store-screens__glow"></div>
		<div class="qeema-store-screens">

		<div class="qeema-store-col qeema-store-col--play">
			<div class="qeema-store-col__label"><span class="dot"></span>Google Play</div>
			<div class="qeema-store-phone">
				<div class="qeema-store-phone__notch"></div>
				<div class="qeema-store-phone__screen">
					<div class="qeema-store-statusbar">
						<span>9:41</span>
						<div class="qeema-store-statusbar__icons">
							<div class="qeema-sb-bar"><span></span><span></span><span></span><span></span></div>
							<div class="qeema-sb-battery"></div>
						</div>
					</div>
					<div class="qeema-play-header">
						<div class="qeema-play-header__top">
							<span class="qeema-play-header__title">Google Play</span>
							<span class="qeema-play-header__avatar"></span>
						</div>
						<div class="qeema-play-search">
							<span class="qeema-play-search__icon"></span>
							<span>ابحث عن تطبيقات</span>
						</div>
					</div>
					<div class="qeema-play-tabs">
						<span class="qeema-play-tab">الرئيسية</span>
						<span class="qeema-play-tab is-active">التطبيقات</span>
						<span class="qeema-play-tab">الألعاب</span>
					</div>
					<div class="qeema-play-section-title">مقترح لك</div>
					<div class="qeema-store-list">
						<div class="qeema-store-track"><?php echo $render_track( $play_apps, false ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in render_row ?></div>
					</div>
					<div class="qeema-store-phone__tabbar">
						<div class="qeema-tabbar-item is-active"><span class="qeema-tabbar-item__icon"></span>التطبيقات</div>
						<div class="qeema-tabbar-item"><span class="qeema-tabbar-item__icon"></span>الألعاب</div>
						<div class="qeema-tabbar-item"><span class="qeema-tabbar-item__icon"></span>البحث</div>
						<div class="qeema-tabbar-item"><span class="qeema-tabbar-item__icon"></span>الأخرى</div>
					</div>
				</div>
			</div>
		</div>

		<div class="qeema-store-col qeema-store-col--apple">
			<div class="qeema-store-col__label"><span class="dot"></span>App Store</div>
			<div class="qeema-store-phone">
				<div class="qeema-store-phone__notch"></div>
				<div class="qeema-store-phone__screen">
					<div class="qeema-store-statusbar">
						<span>9:41</span>
						<div class="qeema-store-statusbar__icons">
							<div class="qeema-sb-bar"><span></span><span></span><span></span><span></span></div>
							<div class="qeema-sb-battery"></div>
						</div>
					</div>
					<div class="qeema-apple-header">
						<div class="qeema-apple-header__title">اليوم</div>
					</div>
					<div class="qeema-apple-feature">
						<?php foreach ( array_slice( $apple_apps, 0, 2 ) as $featured ) : ?>
							<div class="qeema-apple-feature__slide">
								<div class="qeema-apple-feature__plate"><img src="<?php echo esc_url( $featured['logo']['url'] ); ?>" alt=""></div>
								<div>
									<div class="qeema-apple-feature__eyebrow">تطبيق اليوم</div>
									<div class="qeema-apple-feature__title"><?php echo esc_html( $featured['name'] ); ?></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="qeema-apple-section-title">قد يعجبك أيضًا</div>
					<div class="qeema-store-list">
						<div class="qeema-store-track"><?php echo $render_track( $apple_apps, true ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in render_row ?></div>
					</div>
					<div class="qeema-store-phone__tabbar">
						<div class="qeema-tabbar-item is-active"><span class="qeema-tabbar-item__icon"></span>اليوم</div>
						<div class="qeema-tabbar-item"><span class="qeema-tabbar-item__icon"></span>الألعاب</div>
						<div class="qeema-tabbar-item"><span class="qeema-tabbar-item__icon"></span>التطبيقات</div>
						<div class="qeema-tabbar-item"><span class="qeema-tabbar-item__icon"></span>البحث</div>
					</div>
				</div>
			</div>
		</div>

		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generic fallback visual for any slide/category without a dedicated
	 * browser/phone/store mockup — only a Font Awesome icon class (plain
	 * TEXT control, matching value-cards/feature-grid/tech-stack) and this
	 * slide's own category badge are real per-slide data, so the card built
	 * around them is a "delivery process" card rather than fabricated
	 * category-specific content: the icon tile represents the category,
	 * beside a 4-step delivery checklist (idea → design → build → launch)
	 * that's true of every category this widget serves, ticking off in
	 * sequence alongside a matching progress rail — a generic, honest way to
	 * dramatize "we build this" without inventing anything specific to the
	 * category itself.
	 */
	private function render_icon_visual( $icon_class, $badge_text = '' ) {
		$title = $badge_text ? $badge_text : 'مشروعك القادم';
		$steps = array( 'تحليل الفكرة', 'تصميم الواجهة', 'التطوير والبرمجة', 'الإطلاق والدعم' );

		$steps_html = '';
		foreach ( $steps as $i => $label ) {
			$steps_html .= '<div class="qt-catcard-step" style="--i:' . esc_attr( $i ) . '">'
				. '<span class="qt-catcard-step__dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"></path></svg></span>'
				. '<span class="qt-catcard-step__label">' . esc_html( $label ) . '</span>'
				. '</div>';
		}

		return '
			<div class="qt-services-glow"></div>
			<div class="qt-catcard-widget">
				<div class="qt-catcard-card">
					<div class="qt-catcard-top">
						<span class="qt-dot r"></span><span class="qt-dot y"></span><span class="qt-dot g"></span>
						<span class="qt-catcard-title">qeematech • ' . esc_html( $title ) . '</span>
						<span class="qt-chip">LIVE</span>
					</div>
					<div class="qt-catcard-body">
						<div class="qt-catcard-icon-tile">
							<span class="qt-catcard-icon-shine-mask"><span class="qt-catcard-icon-shine"></span></span>
							<i class="' . esc_attr( $icon_class ) . '"></i>
						</div>
						<div class="qt-catcard-rail"><span class="qt-catcard-rail__fill"></span></div>
						<div class="qt-catcard-steps">' . $steps_html . '</div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-svc-a">حلول مخصصة</div>
			<div class="qt-float-chip chip-svc-b">جودة عالية</div>
			<div class="qt-float-chip chip-svc-c">دعم متواصل</div>
		';
	}
}
