<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Homepage portfolio gallery matching the approved mockup: badge + filters,
 * glass cards with device frames, dual CTAs, show-more.
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
		$this->add_control( 'badge', array(
			'label'   => __( 'Badge', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'معرض المشاريع الرقمية و البرمجية',
		) );
		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'أعمالنا ومشروعاتنا',
		) );
		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'تقدم شركة قيمة تك أفضل الحلول والعروض المتاحة لـ تصميم المواقع والمتاجر الإلكترونية وتطبيقات الهاتف وفق المعايير العالمية',
		) );
		$this->add_control( 'footer_note', array(
			'label'   => __( 'Footer Note', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'لديك فكرة مشروع معينة؟ فريقنا البرمجي جاهز لتحويلها إلى تطبيق ناجح. تواصل مع مستشارك التقني',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'categories_section', array(
			'label' => __( 'Category Links', 'qeematech-elementor-widgets' ),
		) );
		$this->add_control( 'categories', array(
			'label'       => __( 'Categories', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array( 'name' => 'label', 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT ),
				array( 'name' => 'link', 'label' => 'Link', 'type' => \Elementor\Controls_Manager::URL ),
				array( 'name' => 'active', 'label' => 'Active (highlighted)', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => '' ),
			),
			'default'     => array(),
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
			'default' => 8,
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
			'label'       => __( 'Buttons', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
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
			'default'     => array(),
			'title_field' => '{{{ text }}}',
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="qeema-portfolio-teaser">
			<div class="qeema-portfolio-teaser__wrap">
				<header class="qeema-portfolio-teaser__head">
					<?php if ( ! empty( $settings['badge'] ) ) : ?>
						<span class="qeema-portfolio-teaser__badge">
							<span class="qeema-portfolio-teaser__badge-icon" aria-hidden="true"></span>
							<?php echo esc_html( $settings['badge'] ); ?>
						</span>
					<?php endif; ?>
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p class="qeema-portfolio-teaser__lead"><?php echo esc_html( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</header>

				<?php if ( ! empty( $settings['categories'] ) ) : ?>
					<div class="qeema-portfolio-teaser__categories" role="tablist">
						<?php foreach ( $settings['categories'] as $cat ) :
							$is_active = 'yes' === ( $cat['active'] ?? '' );
							?>
							<a class="qeema-portfolio-teaser__cat<?php echo $is_active ? ' active' : ''; ?>" <?php echo ! empty( $cat['link']['url'] ) ? 'href="' . esc_url( $cat['link']['url'] ) . '"' : ''; ?>>
								<?php echo esc_html( $cat['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php $this->render_grid( $settings ); ?>

				<?php if ( ! empty( $settings['buttons'] ) ) : ?>
					<div class="qeema-portfolio-teaser__actions">
						<?php foreach ( $settings['buttons'] as $button ) : ?>
							<a class="qeema-portfolio-teaser__btn <?php echo esc_attr( $button['style'] ); ?>" <?php echo ! empty( $button['link']['url'] ) ? 'href="' . esc_url( $button['link']['url'] ) . '"' : ''; ?>>
								<?php echo esc_html( $button['text'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $settings['footer_note'] ) ) : ?>
					<p class="qeema-portfolio-teaser__foot"><?php echo esc_html( $settings['footer_note'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	private function render_grid( $settings ) {
		$filter_categories = $settings['filter_categories'] ?? '';
		$initial_count     = (int) ( $settings['initial_visible_count'] ?? 0 );
		$show_more_text    = $settings['show_more_text'] ?? 'عرض المزيد';

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
			<div class="qeema-portfolio-grid__wrap">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$post_id  = get_the_ID();
					$image_id = get_post_thumbnail_id( $post_id );
					if ( ! $image_id ) {
						continue;
					}

					$terms = get_the_terms( $post_id, 'portfolio-categories' );
					$cats  = array();
					if ( $terms && ! is_wp_error( $terms ) ) {
						foreach ( $terms as $term ) {
							$cats[] = urldecode( $term->slug );
						}
					}

					$is_app   = in_array( 'تطبيقات-الهاتف', $cats, true );
					$item_cls = 'qeema-portfolio-grid__item' . ( $is_app ? ' qeema-portfolio-grid__item--app' : '' );
					?>
					<div class="<?php echo esc_attr( $item_cls ); ?>" data-cats="<?php echo esc_attr( implode( ' ', $cats ) ); ?>">
						<?php echo $is_app ? $this->render_phone_card( $post_id, $image_id ) : $this->render_browser_card( $post_id, $image_id ); ?>
					</div>
				<?php endwhile; ?>
			</div>
			<?php if ( $show_more ) : ?>
				<button type="button" class="qeema-portfolio-teaser__btn primary qeema-portfolio-grid__show-more">
					<?php echo esc_html( $show_more_text ); ?>
					<span aria-hidden="true">←</span>
				</button>
			<?php endif; ?>
		</div>
		<?php
		wp_reset_postdata();
	}

	private function card_desc( $post_id ) {
		$excerpt = get_the_excerpt( $post_id );
		if ( $excerpt ) {
			return wp_trim_words( wp_strip_all_tags( $excerpt ), 12, '…' );
		}
		$challenge = function_exists( 'get_field' ) ? get_field( 'التحدي', $post_id ) : '';
		if ( is_string( $challenge ) && $challenge ) {
			return wp_trim_words( wp_strip_all_tags( $challenge ), 12, '…' );
		}
		$terms = get_the_terms( $post_id, 'portfolio-categories' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->name;
		}
		return '';
	}

	private function render_browser_card( $post_id, $image_id ) {
		$permalink = get_permalink( $post_id );
		$external  = function_exists( 'get_field' ) ? get_field( 'link', $post_id ) : '';
		$desc      = $this->card_desc( $post_id );
		$domain    = '';
		if ( $external ) {
			$host   = wp_parse_url( $external, PHP_URL_HOST );
			$domain = $host ? preg_replace( '/^www\./', '', $host ) : '';
		}
		ob_start();
		?>
		<article class="qeema-portfolio-grid__card qeema-portfolio-grid__browser-card">
			<div class="qeema-portfolio-grid__stage">
				<div class="qeema-portfolio-grid__browser">
					<div class="qeema-portfolio-grid__browser-bar">
						<span class="qt-dot r"></span><span class="qt-dot y"></span><span class="qt-dot g"></span>
						<?php if ( $domain ) : ?>
							<span class="qeema-portfolio-grid__browser-url"><?php echo esc_html( $domain ); ?></span>
						<?php endif; ?>
					</div>
					<a class="qeema-portfolio-grid__browser-screen" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
						<?php
						echo wp_get_attachment_image( $image_id, 'large', false, array(
							'class'    => 'qeema-portfolio-grid__media',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'sizes'    => '(max-width:820px) 45vw, 280px',
						) );
						?>
					</a>
				</div>
			</div>
			<div class="qeema-portfolio-grid__browser-footer">
				<h3 class="qeema-portfolio-grid__browser-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<?php if ( $desc ) : ?>
					<p class="qeema-portfolio-grid__browser-desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
				<div class="qeema-portfolio-grid__browser-actions">
					<a class="qeema-portfolio-grid__browser-btn ghost" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'عرض المشروع', 'qeematech-elementor-widgets' ); ?></a>
				</div>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}

	private function render_phone_card( $post_id, $image_id ) {
		$permalink = get_permalink( $post_id );
		$android   = function_exists( 'get_field' ) ? get_field( 'android', $post_id ) : '';
		$ios       = function_exists( 'get_field' ) ? get_field( 'ios', $post_id ) : '';
		$store_url = $android ? $android : $ios;
		$desc      = $this->card_desc( $post_id );
		ob_start();
		?>
		<article class="qeema-portfolio-grid__card qeema-portfolio-grid__phone-card">
			<div class="qeema-portfolio-grid__stage">
				<a class="qeema-portfolio-grid__shot" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
					<?php
					/* Thumbnails already include device chrome — do not wrap in another bezel. */
					echo wp_get_attachment_image( $image_id, 'full', false, array(
						'class'    => 'qeema-portfolio-grid__media',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => '(max-width:560px) 70vw, 220px',
					) );
					?>
				</a>
			</div>
			<div class="qeema-portfolio-grid__browser-footer">
				<h3 class="qeema-portfolio-grid__browser-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<?php if ( $desc ) : ?>
					<p class="qeema-portfolio-grid__browser-desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
				<div class="qeema-portfolio-grid__browser-actions">
					<a class="qeema-portfolio-grid__browser-btn ghost" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'عرض المشروع', 'qeematech-elementor-widgets' ); ?></a>
				</div>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}
}
