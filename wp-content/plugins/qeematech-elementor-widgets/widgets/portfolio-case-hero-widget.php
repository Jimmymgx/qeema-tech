<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hero for the single-portfolio case-study template. Reads the CURRENT
 * portfolio post at render time (this widget is attached via an Elementor
 * Theme Builder condition, not hand-placed per post) — service/client/link
 * from real ACF fields, banner image falling back to the featured image.
 * No fabricated copy: a field that's empty on a given post just doesn't
 * render its chip/button.
 */
class Qeema_Portfolio_Case_Hero_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-portfolio-case-hero';
	}

	public function get_title() {
		return __( 'Portfolio Case Hero', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-single-page';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-scroll-reveal' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Content', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'quote_link', array(
			'label'   => __( 'Request-Quote Button Link', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '/أتصل-بنا/' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$settings = $this->get_settings_for_display();

		$service = get_field( 'الخدمة', $post_id );
		$client  = get_field( 'العميل', $post_id );
		$link    = get_field( 'link', $post_id );
		$android = get_field( 'android', $post_id );
		$ios     = get_field( 'ios', $post_id );

		// App-only projects have no website link, just store links - the
		// "view project" button should still work, falling back in the same
		// order live uses (website, then whichever store link exists).
		$project_url = $link ? $link : ( $android ? $android : $ios );

		$banner_id = get_field( 'banner', $post_id );
		$image_id  = $banner_id ? $banner_id : get_post_thumbnail_id( $post_id );

		$quote_url = ! empty( $settings['quote_link']['url'] ) ? $settings['quote_link']['url'] : '/أتصل-بنا/';
		?>
		<section class="qeema-portfolio-case-hero">
			<div class="qeema-portfolio-case-hero__glow"></div>
			<div class="qeema-portfolio-case-hero__blob qeema-portfolio-case-hero__blob--a"></div>
			<div class="qeema-portfolio-case-hero__blob qeema-portfolio-case-hero__blob--b"></div>

			<div class="qeema-portfolio-case-hero__content qeema-reveal">
				<?php if ( $service ) : ?>
					<span class="qeema-portfolio-case-hero__eyebrow"><?php echo esc_html( $service ); ?></span>
				<?php endif; ?>

				<h1><?php the_title(); ?></h1>

				<?php if ( $client || $link ) : ?>
					<div class="qeema-portfolio-case-hero__meta">
						<?php if ( $client ) : ?>
							<span class="qeema-portfolio-case-hero__chip">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
								<?php echo esc_html( $client ); ?>
							</span>
						<?php endif; ?>
						<?php if ( $link ) : ?>
							<span class="qeema-portfolio-case-hero__chip" dir="ltr">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
								<?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $link ) ) ); ?>
							</span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="qeema-portfolio-case-hero__ctas">
					<?php if ( $project_url ) : ?>
						<a class="qeema-portfolio-case-hero__btn primary" href="<?php echo esc_url( $project_url ); ?>" target="_blank" rel="noopener">
							مشاهدة المشروع ↗
						</a>
					<?php endif; ?>
					<a class="qeema-portfolio-case-hero__btn <?php echo $project_url ? 'ghost' : 'primary'; ?>" href="<?php echo esc_url( $quote_url ); ?>">
						طلب عرض سعر
					</a>
				</div>
			</div>

			<?php if ( $image_id ) : ?>
				<div class="qeema-portfolio-case-hero__wrap">
					<div class="qeema-portfolio-case-hero__banner qeema-reveal" style="--reveal-delay:.15s">
						<div class="qeema-portfolio-case-hero__banner-inner">
							<?php echo wp_get_attachment_image( $image_id, 'large', false, array(
								'class'         => 'qeema-portfolio-case-hero__banner-img',
								'alt'           => get_the_title( $post_id ),
								'loading'       => 'eager',
								'fetchpriority' => 'high',
								'decoding'      => 'async',
							) ); ?>
							<div class="qeema-portfolio-case-hero__banner-sheen"></div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<div class="qeema-portfolio-case-hero__scroll-cue" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
			</div>
		</section>
		<?php
	}
}
