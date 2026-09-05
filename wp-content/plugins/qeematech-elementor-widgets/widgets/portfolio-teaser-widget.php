<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Portfolio teaser section — heading, subtext, category badges linking out
 * to the full portfolio page, and 2 CTA buttons. Production's version paired
 * this with an ElementsKit tab widget whose live dynamic-content behavior
 * couldn't be confirmed from the data alone, so this rebuilds it as static
 * category links to the portfolio archive instead of guessing at an inline
 * filtering system.
 */
class Qeema_Portfolio_Teaser_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-portfolio-teaser';
	}

	public function get_title() {
		return __( 'Portfolio Teaser', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-portfolio-grid' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Content', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'أعمالنا ومشروعاتنا',
		) );
		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'تقدم شركة قيمة تك أفضل الحلول والعروض المتاحة لـ تصميم المواقع',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'categories_section', array(
			'label' => __( 'Category Links', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'categories', array(
			'label'   => __( 'Categories', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => array(
				array( 'name' => 'label', 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT ),
				array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
				array( 'name' => 'active', 'label' => 'Active (highlighted)', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => '' ),
			),
			'default' => array(),
			'title_field' => '{{{ label }}}',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'grid_settings_section', array(
			'label' => __( 'Grid Settings', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'filter_categories', array(
			'label'       => __( 'Limit Grid to Categories (comma-separated slugs, empty = all)', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => 'مواقع-الشركات,مواقع-تعليمية',
		) );
		$this->add_control( 'initial_visible_count', array(
			'label'   => __( 'Initially Visible Items (0 = show all)', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 0,
			'min'     => 0,
		) );
		$this->add_control( 'show_more_text', array(
			'label'   => __( 'Show More Button Text', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'عرض المزيد',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'buttons_section', array(
			'label' => __( 'Buttons', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'buttons', array(
			'label'   => __( 'Buttons', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => array(
				array( 'name' => 'text', 'label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXT ),
				array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
				array(
					'name'    => 'style',
					'label'   => 'Style',
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'primary',
					'options' => array( 'primary' => 'Primary', 'ghost' => 'Ghost' ),
				),
			),
			'default' => array(),
			'title_field' => '{{{ text }}}',
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="qeema-portfolio-teaser">
			<div class="qeema-portfolio-teaser__wrap">
				<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
				<?php if ( ! empty( $settings['subheading'] ) ) : ?>
					<p><?php echo esc_html( $settings['subheading'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $settings['categories'] ) ) : ?>
					<div class="qeema-portfolio-teaser__categories">
						<?php foreach ( $settings['categories'] as $cat ) :
							$is_active = 'yes' === ( $cat['active'] ?? '' );
							?>
							<a class="qeema-portfolio-teaser__cat<?php echo $is_active ? ' active' : ''; ?>" <?php echo ! empty( $cat['link']['url'] ) ? 'href="' . esc_url( $cat['link']['url'] ) . '"' : ''; ?>>
								<?php echo esc_html( $cat['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php $this->render_grid( $settings['filter_categories'], (int) $settings['initial_visible_count'], $settings['show_more_text'] ); ?>

				<?php if ( ! empty( $settings['buttons'] ) ) : ?>
					<div class="qeema-portfolio-teaser__actions">
						<?php foreach ( $settings['buttons'] as $button ) : ?>
							<a class="qeema-portfolio-teaser__btn <?php echo esc_attr( $button['style'] ); ?>" <?php echo ! empty( $button['link']['url'] ) ? 'href="' . esc_url( $button['link']['url'] ) . '"' : ''; ?>>
								<?php echo esc_html( $button['text'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Actual project cards for the section, pulled live from the `portfolio`
	 * CPT populated by the production content import. Filtered client-side
	 * (assets/js/portfolio-grid.js) by reading each category tab's link slug -
	 * no shared PHP data model needed between the tabs above and the cards
	 * here.
	 */
	private function render_grid( $filter_categories = '', $initial_count = 0, $show_more_text = '' ) {
		$query_args = array(
			'post_type'      => 'portfolio',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		);

		$filter_slugs = array_filter( array_map( 'trim', explode( ',', (string) $filter_categories ) ) );
		if ( $filter_slugs ) {
			$query_args['tax_query'] = array( array(
				'taxonomy' => 'portfolio-categories',
				'field'    => 'slug',
				// These slugs are stored percent-encoded by sanitize_title() (see the
				// urldecode() note below) - re-encode the plain Arabic slug the same
				// way so it matches what's actually in the terms table.
				'terms'    => array_map( 'sanitize_title', $filter_slugs ),
				'operator' => 'IN',
			) );
		}

		$query = new \WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return;
		}

		$show_more = $initial_count > 0 && $query->post_count > $initial_count;
		?>
		<div class="qeema-portfolio-grid"<?php echo $initial_count > 0 ? ' data-initial-count="' . esc_attr( $initial_count ) . '"' : ''; ?>>
			<p class="qeema-portfolio-grid__count" aria-live="polite"></p>
			<div class="qeema-portfolio-grid__wrap">
				<?php while ( $query->have_posts() ) : $query->the_post();
					$post_id = get_the_ID();

					$image_id = get_post_thumbnail_id( $post_id );
					if ( ! $image_id ) {
						continue;
					}

					$terms = get_the_terms( $post_id, 'portfolio-categories' );
					$cats  = array();
					if ( $terms && ! is_wp_error( $terms ) ) {
						foreach ( $terms as $term ) {
							// Term slugs were stored percent-encoded by sanitize_title()
							// for these Arabic terms; decode so this matches the plain
							// slug the tab-filtering JS derives from each tab's href.
							$cats[] = urldecode( $term->slug );
						}
					}

					// Both card types render as the same mockup-frame shape
					// with the same footer/button markup (see
					// render_browser_card()/render_phone_card()) so a website
					// card and an app card always come out the same size with
					// identically styled buttons - only the top chrome and
					// the primary button's link differ.
					$is_app   = in_array( 'تطبيقات-الهاتف', $cats, true );
					$item_cls = 'qeema-portfolio-grid__item' . ( $is_app ? ' qeema-portfolio-grid__item--app' : '' );
					?>
					<div class="<?php echo esc_attr( $item_cls ); ?>" data-cats="<?php echo esc_attr( implode( ' ', $cats ) ); ?>">
						<?php echo $is_app ? $this->render_phone_card( $post_id, $image_id ) : $this->render_browser_card( $post_id, $image_id ); ?>
					</div>
				<?php endwhile; ?>
			</div>
			<?php if ( $show_more ) : ?>
				<button type="button" class="qeema-portfolio-teaser__btn primary qeema-portfolio-grid__show-more"><?php echo esc_html( $show_more_text ); ?></button>
			<?php endif; ?>
		</div>
		<?php
		wp_reset_postdata();
	}

	/**
	 * Browser-window mockup card for a real website project — the same visual
	 * language as qeema-browser-showcase (traffic-light dots + fake URL bar)
	 * adapted into a static grid card. The real external client URL comes
	 * from the "Link" ACF field already populated on ~104 of these posts by
	 * the production import; when it's empty (no real URL to show), the
	 * "زيارة الموقع" button is simply omitted rather than fabricating one —
	 * the internal case-study link is always real and always shown.
	 */
	private function render_browser_card( $post_id, $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'large' );
		$permalink = get_permalink( $post_id );
		$external  = function_exists( 'get_field' ) ? get_field( 'link', $post_id ) : '';
		$domain    = '';
		if ( $external ) {
			$host   = wp_parse_url( $external, PHP_URL_HOST );
			$domain = $host ? preg_replace( '/^www\./', '', $host ) : '';
		}
		ob_start();
		?>
		<div class="qeema-portfolio-grid__browser-card">
			<div class="qeema-portfolio-grid__browser-bar">
				<span class="qt-dot r"></span><span class="qt-dot y"></span><span class="qt-dot g"></span>
				<?php if ( $domain ) : ?>
					<span class="qeema-portfolio-grid__browser-url"><?php echo esc_html( $domain ); ?></span>
				<?php endif; ?>
			</div>
			<a class="qeema-portfolio-grid__browser-screen" href="<?php echo esc_url( $permalink ); ?>" style="background-image:url('<?php echo esc_url( $image_url ); ?>')" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"></a>
			<div class="qeema-portfolio-grid__browser-footer">
				<h3 class="qeema-portfolio-grid__browser-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<div class="qeema-portfolio-grid__browser-actions">
					<?php if ( $external ) : ?>
						<a class="qeema-portfolio-grid__browser-btn primary" href="<?php echo esc_url( $external ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'زيارة الموقع', 'qeematech-elementor-widgets' ); ?></a>
					<?php endif; ?>
					<a class="qeema-portfolio-grid__browser-btn ghost" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'عرض المشروع', 'qeematech-elementor-widgets' ); ?></a>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Phone mockup card for a real mobile-app project — the same footer,
	 * title and 2-button markup as render_browser_card() (so both card
	 * types show identically styled buttons), just with a phone notch
	 * instead of browser chrome. The primary button opens the app's real
	 * Google Play or App Store listing (the "android"/"ios" ACF fields
	 * already populated by the production import on 14 of these 50 posts);
	 * when neither is set, that button is simply omitted rather than
	 * fabricating a store link — the internal case-study link is always
	 * real and always shown.
	 */
	private function render_phone_card( $post_id, $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'large' );
		$permalink = get_permalink( $post_id );
		$android   = function_exists( 'get_field' ) ? get_field( 'android', $post_id ) : '';
		$ios       = function_exists( 'get_field' ) ? get_field( 'ios', $post_id ) : '';
		$store_url = $android ? $android : $ios;
		ob_start();
		?>
		<div class="qeema-portfolio-grid__phone-card">
			<div class="qeema-portfolio-grid__browser-bar qeema-portfolio-grid__phone-bar">
				<span class="qeema-portfolio-grid__phone-notch"></span>
			</div>
			<a class="qeema-portfolio-grid__browser-screen" href="<?php echo esc_url( $permalink ); ?>" style="background-image:url('<?php echo esc_url( $image_url ); ?>')" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"></a>
			<div class="qeema-portfolio-grid__browser-footer">
				<h3 class="qeema-portfolio-grid__browser-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<div class="qeema-portfolio-grid__browser-actions">
					<?php if ( $store_url ) : ?>
						<a class="qeema-portfolio-grid__browser-btn primary" href="<?php echo esc_url( $store_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'تحميل التطبيق', 'qeematech-elementor-widgets' ); ?></a>
					<?php endif; ?>
					<a class="qeema-portfolio-grid__browser-btn ghost" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'عرض المشروع', 'qeematech-elementor-widgets' ); ?></a>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
