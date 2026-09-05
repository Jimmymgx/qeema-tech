<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hero section for About-style pages — same content shape as the homepage's
 * Hero Section widget (heading/subheading/buttons) plus an eyebrow/badge
 * above the heading, paired with the floating phone-mockups visual. Kept as
 * its own widget rather than extending `Qeema_Hero_Section_Widget` so the
 * homepage's hero is never at risk of being affected by About-page-specific
 * changes.
 */
class Qeema_About_Hero_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-about-hero';
	}

	public function get_title() {
		return __( 'About Hero', 'qeematech-elementor-widgets' );
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

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow / Badge', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'من نحن',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'نبني تجارب رقمية احترافية تساعد أعمالك على الانطلاق',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'قيمة تك شركة متخصصة في تطوير المواقع والتطبيقات والأنظمة الذكية، نساعد الشركات على تحويل أفكارها إلى منتجات رقمية حقيقية.',
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
			'default' => 'phone_mockups',
			'options' => array(
				'none'           => __( 'None', 'qeematech-elementor-widgets' ),
				'phone_mockups'  => __( 'Floating Phone Mockups', 'qeematech-elementor-widgets' ),
				'service_orbit'  => __( 'Animated Service Orbit', 'qeematech-elementor-widgets' ),
				'client_reviews' => __( 'Animated Client Reviews Reel', 'qeematech-elementor-widgets' ),
				'blog_reel'      => __( 'Animated Latest Articles Reel', 'qeematech-elementor-widgets' ),
				'client_wall'    => __( 'Animated Client Logo Wall', 'qeematech-elementor-widgets' ),
				'construction_build' => __( 'Animated Rising Building (Construction)', 'qeematech-elementor-widgets' ),
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
					'default' => 'اطلب مشروعك',
				),
				array(
					'name'  => 'link',
					'label' => __( 'Link', 'qeematech-elementor-widgets' ),
					'type'  => \Elementor\Controls_Manager::URL,
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
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$has_visual  = 'none' !== $settings['visual_variant'];
		$wrap_class  = 'qeema-hero-section__wrap' . ( $has_visual ? '' : ' qeema-hero--no-visual' );
		$heading_tag = in_array( $settings['heading_tag'] ?? 'h1', array( 'h1', 'h2', 'h3' ), true ) ? $settings['heading_tag'] : 'h1';
		?>
		<section class="qeema-hero-section qeema-about-hero">
			<div class="<?php echo esc_attr( $wrap_class ); ?>">
				<div class="qeema-hero-section__content">
					<?php if ( ! empty( $settings['eyebrow'] ) ) : ?>
						<span class="qeema-hero-section__eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></span>
					<?php endif; ?>
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

				<?php if ( 'phone_mockups' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_phone_mockups(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'service_orbit' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_service_orbit(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php elseif ( 'client_reviews' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_client_reviews(); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped real post data ?>
					</div>
				<?php elseif ( 'blog_reel' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_blog_reel(); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped real post data ?>
					</div>
				<?php elseif ( 'client_wall' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_client_wall(); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped real logo data ?>
					</div>
				<?php elseif ( 'construction_build' === $settings['visual_variant'] ) : ?>
					<div class="qeema-hero-section__visual">
						<?php echo $this->render_construction_build(); // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Floating phone-screen mockups + label chips — moved here verbatim from
	 * the homepage's Hero Section widget, since this is the visual it was
	 * actually built for (see that widget's own history).
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
	 * Services-page hero visual: a glass card that cycles through the site's
	 * actual service list (icon + name cross-fading), echoing the chat-bubble
	 * hero's visual language but themed around "what we offer" rather than a
	 * conversation. Service list mirrors the feature-grid cards below it on
	 * the Services page rather than inventing separate copy.
	 */
	private function render_service_orbit() {
		$services = array(
			array( 'icon' => 'fas fa-laptop-code', 'label' => 'تصميم المواقع الإلكترونية' ),
			array( 'icon' => 'fas fa-mobile-alt', 'label' => 'تطوير تطبيقات الموبايل' ),
			array( 'icon' => 'fas fa-store', 'label' => 'المتاجر الإلكترونية' ),
			array( 'icon' => 'fas fa-bullhorn', 'label' => 'التسويق الإلكتروني' ),
			array( 'icon' => 'fas fa-code', 'label' => 'مشاريع برمجة خاصة' ),
			array( 'icon' => 'fas fa-sitemap', 'label' => 'أنظمة ERP' ),
		);

		$dots = '';
		foreach ( $services as $i => $service ) {
			$dots .= '<span class="qt-services-dot' . ( 0 === $i ? ' is-active' : '' ) . '"></span>';
		}

		return '
			<div class="qt-services-glow"></div>
			<div class="qt-services-widget" data-qt-services="' . esc_attr( wp_json_encode( $services, JSON_UNESCAPED_UNICODE ) ) . '">
				<div class="qt-services-card">
					<div class="qt-services-top">
						<span class="qt-chat-status-dot"></span>
						<span class="qt-chat-title">Qeematech • خدماتنا الرقمية</span>
					</div>
					<div class="qt-services-body">
						<div class="qt-services-slide">
							<div class="qt-services-icon-wrap"><i class="' . esc_attr( $services[0]['icon'] ) . ' qt-services-icon"></i></div>
							<div class="qt-services-name">' . esc_html( $services[0]['label'] ) . '</div>
						</div>
						<div class="qt-services-dots">' . $dots . '</div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-svc-a"><i class="fas fa-server"></i> استضافة</div>
			<div class="qt-float-chip chip-svc-b"><i class="fas fa-search"></i> SEO</div>
			<div class="qt-float-chip chip-svc-c"><i class="fas fa-handshake"></i> CRM</div>
		';
	}

	/**
	 * Client Reviews page hero visual — built entirely from real
	 * `testimonial` CPT data (real client photos + the live published count)
	 * since that CPT has no rating/quote/name fields to draw from; per this
	 * project's established rule, nothing here is fabricated. A spotlight
	 * ring animates across the real photos in pure CSS (same technique as
	 * the CRM page's pipeline mover) — no JS needed.
	 */
	private function render_client_reviews() {
		$query = new \WP_Query( array(
			'post_type'      => 'testimonial',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		) );

		$avatars = '';
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id   = get_the_ID();
			$image_id  = get_post_meta( $post_id, 'client_image', true );
			$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : get_the_post_thumbnail_url( $post_id, 'thumbnail' );
			if ( ! $image_url ) {
				continue;
			}
			$avatars .= '<span class="qt-reviews-avatar"><img src="' . esc_url( $image_url ) . '" alt="" loading="lazy">'
				. '<span class="qt-reviews-avatar__play"><svg viewBox="0 0 24 24"><polygon points="9,7 18,12 9,17" fill="currentColor"/></svg></span></span>';
		}
		wp_reset_postdata();

		if ( ! $avatars ) {
			return '';
		}

		$total = (int) wp_count_posts( 'testimonial' )->publish;

		return '
			<div class="qt-reviews-glow"></div>
			<div class="qt-reviews-widget">
				<div class="qt-reviews-card">
					<div class="qt-reviews-top">
						<span class="qt-dot r"></span><span class="qt-dot y"></span><span class="qt-dot g"></span>
						<span class="qt-reviews-title">qeematech • آراء العملاء</span>
						<span class="qt-chip">فيديو حقيقي</span>
					</div>
					<div class="qt-reviews-body">
						<div class="qt-reviews-avatars">
							' . $avatars . '
							<span class="qt-reviews-spotlight"></span>
						</div>
						<div class="qt-reviews-stat">
							<span class="qt-reviews-stat__num">' . esc_html( $total ) . '</span>
							<span class="qt-reviews-stat__label">فيديو حقيقي من عملائنا</span>
						</div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-reviews-a">عملاء حقيقيون</div>
			<div class="qt-float-chip chip-reviews-b">بدون سكريبت</div>
			<div class="qt-float-chip chip-reviews-c">فيديو موثق</div>
		';
	}

	/**
	 * Blog archive hero visual — reuses the exact `.qt-services-widget` cycling
	 * card shell/JS from render_service_orbit() above (that widget already
	 * reads any {icon,label} array from the `data-qt-services` attribute, not
	 * just the hardcoded services list, so no JS changes are needed) but
	 * cycles through the site's real latest published post titles instead of
	 * a fixed services list. One consistent icon is used for every entry
	 * since individual posts have no per-post icon field — that's a decorative
	 * choice, not fabricated data. Falls back silently (renders nothing) if
	 * there are no published posts yet.
	 */
	private function render_blog_reel() {
		$query = new \WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );

		$articles = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$articles[] = array(
				'icon'  => 'fas fa-newspaper',
				'label' => get_the_title(),
			);
		}
		wp_reset_postdata();

		if ( ! $articles ) {
			return '';
		}

		$dots = '';
		foreach ( $articles as $i => $article ) {
			$dots .= '<span class="qt-services-dot' . ( 0 === $i ? ' is-active' : '' ) . '"></span>';
		}

		return '
			<div class="qt-services-glow"></div>
			<div class="qt-services-widget" data-qt-services="' . esc_attr( wp_json_encode( $articles, JSON_UNESCAPED_UNICODE ) ) . '">
				<div class="qt-services-card">
					<div class="qt-services-top">
						<span class="qt-chat-status-dot"></span>
						<span class="qt-chat-title">Qeematech • آخر المقالات</span>
					</div>
					<div class="qt-services-body">
						<div class="qt-services-slide">
							<div class="qt-services-icon-wrap"><i class="' . esc_attr( $articles[0]['icon'] ) . ' qt-services-icon"></i></div>
							<div class="qt-services-name">' . esc_html( $articles[0]['label'] ) . '</div>
						</div>
						<div class="qt-services-dots">' . $dots . '</div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-svc-a">تطوير مواقع</div>
			<div class="qt-float-chip chip-svc-b">تطبيقات الجوال</div>
			<div class="qt-float-chip chip-svc-c">تسويق إلكتروني</div>
		';
	}

	/**
	 * Our-Clients page hero visual — a small "living" wall of real client
	 * logos (same ACF `client` repeater the Trusted-By widget reads) sitting
	 * beside the hero text: a 3x3 tile grid where each tile idles with its
	 * own soft pulse (staggered via the `--i` custom property) while a light
	 * beam sweeps across the whole card, plus a real, computed logo count in
	 * the header chip — never a fabricated number.
	 */
	private function render_client_wall() {
		if ( ! function_exists( 'get_field' ) ) {
			return '';
		}

		$clients  = get_field( 'client', 'option' );
		$logo_ids = array();
		if ( ! empty( $clients ) && is_array( $clients ) ) {
			foreach ( $clients as $row ) {
				if ( ! empty( $row['logo'] ) ) {
					$logo_ids[] = (int) $row['logo'];
				}
			}
		}

		if ( ! $logo_ids ) {
			return '';
		}

		$total = count( $logo_ids );
		$tiles = '';
		foreach ( array_slice( $logo_ids, 0, 9 ) as $i => $id ) {
			$url = wp_get_attachment_image_url( $id, 'thumbnail' );
			if ( ! $url ) {
				continue;
			}
			$tiles .= '<span class="qt-clients-tile" style="--i:' . esc_attr( $i ) . '"><img src="' . esc_url( $url ) . '" alt="" loading="lazy"></span>';
		}

		if ( ! $tiles ) {
			return '';
		}

		return '
			<div class="qt-clients-glow"></div>
			<div class="qt-clients-widget">
				<div class="qt-clients-card">
					<div class="qt-clients-top">
						<span class="qt-dot r"></span><span class="qt-dot y"></span><span class="qt-dot g"></span>
						<span class="qt-clients-title">qeematech • عملاؤنا</span>
						<span class="qt-chip">+' . esc_html( $total ) . ' عميل</span>
					</div>
					<div class="qt-clients-grid">
						' . $tiles . '
						<span class="qt-clients-sweep"></span>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-clients-a">عملاء حقيقيون</div>
			<div class="qt-float-chip chip-clients-b">ثقة مستمرة</div>
			<div class="qt-float-chip chip-clients-c">نمو مشترك</div>
		';
	}

	/**
	 * Contracting/construction-company page hero visual — a small building
	 * rising floor by floor (foundation first, roof last) with its windows
	 * lighting on as each floor completes, and a crane idling beside it,
	 * dramatizing this page's own pitch (a company site "built" for a
	 * construction firm) instead of reusing a generic mockup.
	 */
	private function render_construction_build() {
		// A slight taper toward the roof (ground floor widest, top floor
		// narrowest) so the tower reads as an actual building under
		// construction rather than a perfectly uniform, machine-drawn stack.
		$widths = array( 150, 150, 140, 140, 126 );
		$floors = '';
		for ( $i = 4; $i >= 0; $i-- ) {
			$floors .= '<span class="qt-build-floor" style="--i:' . esc_attr( $i ) . ';width:' . esc_attr( $widths[ $i ] ) . 'px"><i></i><i></i><i></i></span>';
		}

		return '
			<div class="qt-build-glow"></div>
			<div class="qt-build-widget">
				<div class="qt-build-card">
					<div class="qt-build-top">
						<span class="qt-dot r"></span><span class="qt-dot y"></span><span class="qt-dot g"></span>
						<span class="qt-build-title">qeematech • موقع شركتك</span>
						<span class="qt-chip">LIVE</span>
					</div>
					<div class="qt-build-scene">
						<div class="qt-build-crane">
							<span class="qt-build-crane__mast"></span>
							<span class="qt-build-crane__flag"></span>
							<span class="qt-build-crane__jib"></span>
							<span class="qt-build-crane__counterjib"></span>
							<span class="qt-build-crane__counterweight"></span>
							<span class="qt-build-crane__cable"></span>
							<span class="qt-build-crane__hook"></span>
						</div>
						<div class="qt-build-tower">
							' . $floors . '
							<span class="qt-build-scaffold"></span>
						</div>
						<span class="qt-build-pile"></span>
						<div class="qt-build-ground"></div>
					</div>
				</div>
			</div>
			<div class="qt-float-chip chip-build-a">تصميم احترافي</div>
			<div class="qt-float-chip chip-build-b">يعكس هويتك</div>
			<div class="qt-float-chip chip-build-c">جاهز لعملائك</div>
		';
	}
}
