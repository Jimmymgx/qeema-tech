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

		$this->add_control( 'heading_tag', array(
			'label'       => __( 'Heading Tag', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::SELECT,
			'default'     => 'h1',
			'options'     => array(
				'h1' => 'H1',
				'h2' => 'H2',
				'h3' => 'H3',
			),
			'description' => __( 'Use H1 only once per page. Switch to H2/H3 when this hero is not the page\'s single main heading.', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'visual_variant', array(
			'label'   => __( 'Decorative Visual', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'code_typing',
			'options' => array(
				'none'          => __( 'None', 'qeematech-elementor-widgets' ),
				'code_typing'   => __( 'Animated Code Typing', 'qeematech-elementor-widgets' ),
				'phone_mockups' => __( 'Floating Phone Mockups', 'qeematech-elementor-widgets' ),
				'chat_bubbles'  => __( 'Animated Chat Bubbles', 'qeematech-elementor-widgets' ),
				'design_build'  => __( 'Animated Wireframe-to-Design Build', 'qeematech-elementor-widgets' ),
				'app_build'     => __( 'Animated App Screen Build (Mobile App Dev)', 'qeematech-elementor-widgets' ),
				'growth_chart'  => __( 'Animated Growth Dashboard (Digital Marketing)', 'qeematech-elementor-widgets' ),
				'server_rack'   => __( 'Animated Server Rack (Web Hosting)', 'qeematech-elementor-widgets' ),
				'store_cart'    => __( 'Animated Storefront Cart (E-commerce)', 'qeematech-elementor-widgets' ),
				'search_rank'   => __( 'Animated Search Ranking (SEO)', 'qeematech-elementor-widgets' ),
				'blueprint'     => __( 'Animated Blueprint Build (Custom Software)', 'qeematech-elementor-widgets' ),
				'pipeline'      => __( 'Animated Sales Pipeline (CRM)', 'qeematech-elementor-widgets' ),
				'module_hub'    => __( 'Animated Module Hub (ODOO ERP)', 'qeematech-elementor-widgets' ),
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
			'default' => array(
				'url' => \Elementor\Utils::get_placeholder_image_src(),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$style = '';
		if ( ! empty( $settings['background_color'] ) ) {
			$style .= 'background-color:' . esc_attr( $settings['background_color'] ) . ';';
		}
		if ( ! empty( $settings['background_image']['url'] ) && \Elementor\Utils::get_placeholder_image_src() !== $settings['background_image']['url'] ) {
			$style .= 'background-image:url(' . esc_url( $settings['background_image']['url'] ) . ');background-size:cover;background-position:center;';
		}

		$has_visual   = 'none' !== $settings['visual_variant'];
		$wrap_class   = 'qeema-hero-section__wrap' . ( $has_visual ? '' : ' qeema-hero--no-visual' );
		$heading_tag  = in_array( $settings['heading_tag'] ?? 'h1', array( 'h1', 'h2', 'h3' ), true ) ? $settings['heading_tag'] : 'h1';
		?>
		<section class="qeema-hero-section" <?php echo $style ? 'style="' . $style . '"' : ''; ?>>
			<div class="<?php echo esc_attr( $wrap_class ); ?>">
				<div class="qeema-hero-section__content">
					<<?php echo $heading_tag; ?> class="qeema-hero-section__heading"><?php echo wp_kses_post( $settings['heading'] ); ?></<?php echo $heading_tag; ?>>
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
				<?php elseif ( 'phone_mockups' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_phone_mockups(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'chat_bubbles' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_chat_bubbles(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'design_build' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_design_build(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'app_build' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_app_build(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'growth_chart' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_growth_chart(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'server_rack' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_server_rack(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'store_cart' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_store_cart(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'search_rank' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_search_rank(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'blueprint' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_blueprint(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'pipeline' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_pipeline(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'module_hub' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_module_hub(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Alternate hero visual: floating phone-screen mockups + label chips,
	 * used on pages that talk about the company/product rather than code
	 * (e.g. About Us) so those pages don't reuse the homepage's exact
	 * "code typing" visual.
	 */
	private function render_phone_mockups() {
		return '
			<div class="qt-phones-glow"></div>
			<div class="qt-phone-mock phone-a">
				<div class="qt-phone-mock__notch"></div>
				<div class="qt-phone-mock__screen">
					<div class="qt-phone-mock__bar"></div>
					<div class="qt-phone-mock__card"></div>
					<div class="qt-phone-mock__card small"></div>
				</div>
			</div>
			<div class="qt-phone-mock phone-b">
				<div class="qt-phone-mock__notch"></div>
				<div class="qt-phone-mock__screen alt">
					<div class="qt-phone-mock__bar"></div>
					<div class="qt-phone-mock__card"></div>
					<div class="qt-phone-mock__card small"></div>
				</div>
			</div>
			<div class="qt-phone-mock phone-c">
				<div class="qt-phone-mock__notch"></div>
				<div class="qt-phone-mock__screen alt2">
					<div class="qt-phone-mock__bar"></div>
					<div class="qt-phone-mock__card"></div>
					<div class="qt-phone-mock__card small"></div>
				</div>
			</div>
			<div class="qt-float-chip chip-a">واجهات احترافية</div>
			<div class="qt-float-chip chip-b">تطوير قوي</div>
			<div class="qt-float-chip chip-c">جاهز للنمو</div>
		';
	}

	/**
	 * Contact-page hero visual: a floating "live chat" glass card that loops
	 * a short conversation — a static incoming client message, a typing
	 * indicator, then a reply that types itself out character-by-character
	 * (same technique as the code-typing variant's qt-typed-code). Built for
	 * the Contact page specifically rather than reusing code_typing/
	 * phone_mockups, since neither visually says "get in touch."
	 */
	private function render_chat_bubbles() {
		$client_text = 'عايز اعرف تفاصيل مشروعي 👋';
		$reply_text  = 'أهلاً بيك! 👋 فريقنا هيرد عليك خلال دقايق ⚡';
		return '
			<div class="qt-chat-glow"></div>
			<div class="qt-chat-widget" data-qt-chat-client="' . esc_attr( $client_text ) . '" data-qt-chat-reply="' . esc_attr( $reply_text ) . '">
				<div class="qt-chat-card">
					<div class="qt-chat-top">
						<span class="qt-chat-status-dot"></span>
						<span class="qt-chat-title">Qeematech • أونلاين الآن</span>
					</div>
					<div class="qt-chat-body">
						<div class="qt-chat-bubble qt-chat-bubble--in">
							<span class="qt-chat-bubble-text qt-chat-client-text"></span>
						</div>
						<div class="qt-chat-typing"><span></span><span></span><span></span></div>
						<div class="qt-chat-bubble qt-chat-bubble--out">
							<span class="qt-chat-bubble-text qt-chat-reply-text"></span><span class="qt-caret"></span>
						</div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-chat-a">واتساب</div>
			<div class="qt-float-chip chip-chat-b">إيميل</div>
			<div class="qt-float-chip chip-chat-c">مكالمة</div>
		';
	}

	/**
	 * Service-page hero visual: a browser-chrome card whose inner layout
	 * blocks loop from flat gray "wireframe" placeholders into filled-in
	 * brand-colored blocks on a stagger, i.e. "from concept to finished
	 * design" - built specifically for the web design/development service
	 * page rather than reusing code_typing's (Home page's) exact content.
	 * Class names are fully unique to this variant (qt-design-*, chip-design-
	 * *) rather than reusing the bare chip-a/b/c names phone_mockups uses,
	 * to avoid the class-name collision that previously broke that variant's
	 * chips (a shared name silently inherited stray CSS properties).
	 */
	/**
	 * "Live design" canvas — instead of every block just pulsing color in
	 * place, the page assembles itself in sequence (image block, then
	 * headline/body/caption lines, then a button) while a small pointer
	 * "drags" down the canvas placing each piece, finishing by clicking the
	 * BUILD chip in the header (which flashes to confirm) — dramatizing this
	 * page's own pitch of watching a real site get designed and shipped.
	 */
	private function render_design_build() {
		return '
			<div class="qt-design-glow"></div>
			<div class="qt-design-widget">
				<div class="qt-design-card">
					<div class="qt-design-top">
						<span class="qt-dot r"></span>
						<span class="qt-dot y"></span>
						<span class="qt-dot g"></span>
						<span class="qt-design-title">qeematech • live design</span>
						<span class="qt-chip qt-design-build-chip">BUILD</span>
					</div>
					<div class="qt-design-canvas">
						<div class="qt-design-block qt-design-block--nav"></div>
						<div class="qt-design-block qt-design-block--hero"></div>
						<div class="qt-design-block qt-design-block--line1"></div>
						<div class="qt-design-block qt-design-block--line2"></div>
						<div class="qt-design-block qt-design-block--line3"></div>
						<div class="qt-design-block qt-design-block--btn"></div>
						<div class="qt-design-swatches">
							<span class="s1"></span><span class="s2"></span><span class="s3"></span>
						</div>
					</div>
					<div class="qt-design-cursor"></div>
				</div>
			</div>
			<div class="qt-float-chip chip-design-a">UI/UX</div>
			<div class="qt-float-chip chip-design-b">متجاوب</div>
			<div class="qt-float-chip chip-design-c">أداء عالي</div>
		';
	}
	/**
	 * Mobile App Development service page: a single phone frame whose screen
	 * assembles itself live (status bar -> nav -> content cards -> a sliding
	 * notification toast), rather than the generic floating-multi-phone
	 * variant, since this page's pitch is "watch an app come together."
	 */
	private function render_app_build() {
		return '
			<div class="qt-appdev-glow"></div>
			<div class="qt-appdev-widget">
				<div class="qt-appdev-phone">
					<div class="qt-appdev-notch"></div>
					<div class="qt-appdev-screen">
						<div class="qt-appdev-statusbar" style="animation-delay:0s"></div>
						<div class="qt-appdev-navbar" style="animation-delay:.2s"></div>
						<div class="qt-appdev-card" style="animation-delay:.5s"></div>
						<div class="qt-appdev-card small" style="animation-delay:.8s"></div>
						<div class="qt-appdev-card" style="animation-delay:1.1s"></div>
						<div class="qt-appdev-toast" style="animation-delay:1.7s">
							<span class="qt-appdev-toast-dot"></span>
							<span class="qt-appdev-toast-text">تحديث جديد</span>
						</div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-appdev-a">iOS</div>
			<div class="qt-float-chip chip-appdev-b">Android</div>
			<div class="qt-float-chip chip-appdev-c">أداء سلس</div>
		';
	}

	/**
	 * Digital Marketing service page: an analytics-dashboard card (rising bar
	 * chart + a filling engagement ring + pulsing reach rings) dramatizing
	 * measurable growth, this page's core pitch.
	 */
	private function render_growth_chart() {
		return '
			<div class="qt-growth-glow"></div>
			<div class="qt-growth-widget">
				<div class="qt-growth-card">
					<div class="qt-growth-top">
						<span class="qt-dot r"></span>
						<span class="qt-dot y"></span>
						<span class="qt-dot g"></span>
						<span class="qt-growth-title">qeematech • growth</span>
						<span class="qt-chip">LIVE</span>
					</div>
					<div class="qt-growth-body">
						<div class="qt-growth-chart">
							<span class="qt-growth-bar" style="--h:34%;animation-delay:0s"></span>
							<span class="qt-growth-bar" style="--h:52%;animation-delay:.15s"></span>
							<span class="qt-growth-bar" style="--h:44%;animation-delay:.3s"></span>
							<span class="qt-growth-bar" style="--h:70%;animation-delay:.45s"></span>
							<span class="qt-growth-bar" style="--h:60%;animation-delay:.6s"></span>
							<span class="qt-growth-bar" style="--h:90%;animation-delay:.75s"></span>
						</div>
						<div class="qt-growth-ring-wrap">
							<div class="qt-growth-ring"></div>
							<div class="qt-growth-ring-core">
								<span class="qt-growth-ring-num">+240%</span>
								<span class="qt-growth-ring-label">ROI</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-growth-a">إعلانات ممولة</div>
			<div class="qt-float-chip chip-growth-b">تحليلات دقيقة</div>
			<div class="qt-float-chip chip-growth-c">وصول أوسع</div>
		';
	}

	/**
	 * Web Hosting service page: a stylized server rack (cascading activity
	 * LEDs, an uptime badge ticking to "99.9%", a pulsing security shield and
	 * a connected globe) speaking directly to reliability/speed/security.
	 */
	private function render_server_rack() {
		return '
			<div class="qt-hosting-glow"></div>
			<div class="qt-hosting-widget">
				<div class="qt-hosting-rack">
					<div class="qt-hosting-unit">
						<span class="qt-hosting-led" style="animation-delay:0s"></span>
						<span class="qt-hosting-led" style="animation-delay:.2s"></span>
						<span class="qt-hosting-led" style="animation-delay:.4s"></span>
						<span class="qt-hosting-bar-mini"></span>
					</div>
					<div class="qt-hosting-unit">
						<span class="qt-hosting-led" style="animation-delay:.1s"></span>
						<span class="qt-hosting-led" style="animation-delay:.3s"></span>
						<span class="qt-hosting-led" style="animation-delay:.5s"></span>
						<span class="qt-hosting-bar-mini alt"></span>
					</div>
					<div class="qt-hosting-unit">
						<span class="qt-hosting-led" style="animation-delay:.2s"></span>
						<span class="qt-hosting-led" style="animation-delay:.4s"></span>
						<span class="qt-hosting-led" style="animation-delay:.6s"></span>
						<span class="qt-hosting-bar-mini"></span>
					</div>
					<div class="qt-hosting-badge">
						<span class="qt-hosting-badge-num">99.9%</span>
						<span class="qt-hosting-badge-label">Uptime</span>
					</div>
				</div>
				<div class="qt-hosting-shield">
					<svg class="qt-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 12 15 16 10"></polyline></svg>
				</div>
				<div class="qt-hosting-globe"></div>
			</div>
			<div class="qt-float-chip chip-hosting-a">SSD NVMe</div>
			<div class="qt-float-chip chip-hosting-b">حماية 24/7</div>
			<div class="qt-float-chip chip-hosting-c">نسخ احتياطي يومي</div>
		';
	}

	/**
	 * E-commerce Store Development service page: a storefront card (product
	 * cards flipping into view, an order-progress sweep, an incrementing cart
	 * badge) dramatizing "browse to checkout" rather than a generic browser
	 * screenshot frame.
	 */
	private function render_store_cart() {
		return '
			<div class="qt-store-glow"></div>
			<div class="qt-store-widget">
				<div class="qt-store-card">
					<div class="qt-store-top">
						<span class="qt-dot r"></span>
						<span class="qt-dot y"></span>
						<span class="qt-dot g"></span>
						<span class="qt-store-title">qeematech • store</span>
						<span class="qt-chip">LIVE</span>
					</div>
					<div class="qt-store-body">
						<div class="qt-store-products">
							<div class="qt-store-product" style="animation-delay:0s"></div>
							<div class="qt-store-product" style="animation-delay:.2s"></div>
							<div class="qt-store-product" style="animation-delay:.4s"></div>
							<div class="qt-store-product" style="animation-delay:.6s"></div>
						</div>
						<div class="qt-store-progress"><span class="qt-store-progress-fill"></span></div>
						<div class="qt-store-progress-label">تم تأكيد الطلب</div>
					</div>
					<div class="qt-store-cart">
						<svg class="qt-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
						<span class="qt-store-cart-badge">3</span>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-store-a">دفع آمن</div>
			<div class="qt-float-chip chip-store-b">شحن سريع</div>
			<div class="qt-float-chip chip-store-c">إدارة سهلة</div>
		';
	}

	/**
	 * SEO service page: a search-interface card (typed query, a magnifier
	 * sweep, and a ranking list climbing to #1 with the client's own row
	 * highlighted) dramatizing "climbing search results" directly.
	 */
	private function render_search_rank() {
		return '
			<div class="qt-seo-glow"></div>
			<div class="qt-seo-widget">
				<div class="qt-seo-card">
					<div class="qt-seo-searchbar">
						<svg class="qt-svg qt-seo-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
						<span class="qt-seo-search-text">تصميم مواقع احترافية<span class="qt-caret"></span></span>
					</div>
					<div class="qt-seo-ranks">
						<div class="qt-seo-rank you" style="animation-delay:0s">
							<span class="qt-seo-rank-num">1</span>
							<span class="qt-seo-rank-bar"><span class="qt-seo-rank-bar-fill you"></span></span>
						</div>
						<div class="qt-seo-rank" style="animation-delay:.15s">
							<span class="qt-seo-rank-num">2</span>
							<span class="qt-seo-rank-bar"><span class="qt-seo-rank-bar-fill" style="width:55%"></span></span>
						</div>
						<div class="qt-seo-rank" style="animation-delay:.3s">
							<span class="qt-seo-rank-num">3</span>
							<span class="qt-seo-rank-bar"><span class="qt-seo-rank-bar-fill" style="width:38%"></span></span>
						</div>
					</div>
					<svg class="qt-svg qt-seo-glass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
				</div>
			</div>
			<div class="qt-float-chip chip-seo-a">#1 نتيجة بحث</div>
			<div class="qt-float-chip chip-seo-b">زيارات عضوية</div>
			<div class="qt-float-chip chip-seo-c">كلمات مفتاحية</div>
		';
	}

	/**
	 * Custom Software Projects service page: an architecture-blueprint card
	 * (nodes + connecting lines drawing themselves in, a turning "processing"
	 * ring) speaking to "solutions engineered from scratch" rather than a
	 * finished-product visual.
	 */
	private function render_blueprint() {
		return '
			<div class="qt-blueprint-glow"></div>
			<div class="qt-blueprint-widget">
				<div class="qt-blueprint-card">
					<div class="qt-blueprint-top">
						<span class="qt-dot r"></span>
						<span class="qt-dot y"></span>
						<span class="qt-dot g"></span>
						<span class="qt-blueprint-title">qeematech • architecture</span>
						<span class="qt-chip">BUILD</span>
					</div>
					<div class="qt-blueprint-canvas">
						<div class="qt-blueprint-node n1" style="animation-delay:0s"></div>
						<div class="qt-blueprint-node n2" style="animation-delay:.3s"></div>
						<div class="qt-blueprint-node n3" style="animation-delay:.6s"></div>
						<div class="qt-blueprint-node n4" style="animation-delay:.9s"></div>
						<svg class="qt-blueprint-lines" viewBox="0 0 240 140" fill="none" aria-hidden="true">
							<path d="M46 28 L120 28" class="qt-blueprint-line" style="animation-delay:.15s"></path>
							<path d="M120 28 L120 84" class="qt-blueprint-line" style="animation-delay:.45s"></path>
							<path d="M120 84 L196 84" class="qt-blueprint-line" style="animation-delay:.75s"></path>
							<path d="M120 84 L120 120" class="qt-blueprint-line" style="animation-delay:1.05s"></path>
						</svg>
						<div class="qt-blueprint-gear"></div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-blueprint-a">حلول مخصصة</div>
			<div class="qt-float-chip chip-blueprint-b">أكواد نظيفة</div>
			<div class="qt-float-chip chip-blueprint-c">قابل للتوسع</div>
		';
	}

	/**
	 * CRM service page: a kanban-style pipeline card (lead -> contacted ->
	 * closed columns with a contact card sliding across them) dramatizing
	 * "organized customer relationships" rather than a generic dashboard.
	 */
	private function render_pipeline() {
		return '
			<div class="qt-pipeline-glow"></div>
			<div class="qt-pipeline-widget">
				<div class="qt-pipeline-card">
					<div class="qt-pipeline-top">
						<span class="qt-dot r"></span>
						<span class="qt-dot y"></span>
						<span class="qt-dot g"></span>
						<span class="qt-pipeline-title">qeematech • CRM</span>
						<span class="qt-chip">PIPELINE</span>
					</div>
					<div class="qt-pipeline-board">
						<div class="qt-pipeline-col">
							<span class="qt-pipeline-col-label">عملاء محتملون</span>
							<span class="qt-pipeline-avatar" style="animation-delay:0s"></span>
						</div>
						<div class="qt-pipeline-col">
							<span class="qt-pipeline-col-label">تم التواصل</span>
							<span class="qt-pipeline-avatar" style="animation-delay:.5s"></span>
						</div>
						<div class="qt-pipeline-col">
							<span class="qt-pipeline-col-label">مغلق</span>
							<span class="qt-pipeline-avatar" style="animation-delay:1s"></span>
						</div>
						<div class="qt-pipeline-mover"></div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-pipeline-a">متابعة تلقائية</div>
			<div class="qt-float-chip chip-pipeline-b">قاعدة بيانات موحدة</div>
			<div class="qt-float-chip chip-pipeline-c">تقارير لحظية</div>
		';
	}

	/**
	 * ODOO ERP service page: separate module tiles sliding in and snapping
	 * together into one central hub, dramatizing "many business processes,
	 * one unified system" — this page's core value proposition.
	 */
	private function render_module_hub() {
		return '
			<div class="qt-erp-glow"></div>
			<div class="qt-erp-widget">
				<div class="qt-erp-hub-core">
					<span class="qt-erp-hub-label">ERP</span>
				</div>
				<div class="qt-erp-module m1" style="animation-delay:0s"></div>
				<div class="qt-erp-module m2" style="animation-delay:.2s"></div>
				<div class="qt-erp-module m3" style="animation-delay:.4s"></div>
				<div class="qt-erp-module m4" style="animation-delay:.6s"></div>
				<div class="qt-erp-module m5" style="animation-delay:.8s"></div>
				<div class="qt-erp-module m6" style="animation-delay:1s"></div>
			</div>
			<div class="qt-float-chip chip-erp-a">وحدات متكاملة</div>
			<div class="qt-float-chip chip-erp-b">إدارة موحدة</div>
			<div class="qt-float-chip chip-erp-c">تقارير شاملة</div>
		';
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
