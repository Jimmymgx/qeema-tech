<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Case-study hero matching the approved وأتموا mockup: sticky mini-bar,
 * split copy + device stage, facts strip, dual CTAs. Uses real ACF/media —
 * no fabricated UI chrome inside the phone screen.
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
			'label'   => __( 'Start-Project Button Link', 'qeematech-elementor-widgets' ),
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

		$settings  = $this->get_settings_for_display();
		$service   = get_field( 'الخدمة', $post_id );
		$client    = get_field( 'العميل', $post_id );
		$link      = get_field( 'link', $post_id );
		$android   = get_field( 'android', $post_id );
		$ios       = get_field( 'ios', $post_id );
		$idea      = get_field( 'idea', $post_id );
		$challenge = get_field( 'التحدي', $post_id );
		$journey   = get_field( 'idea_copy2', $post_id );
		$banner_id = get_field( 'banner', $post_id );
		$thumb_id  = get_post_thumbnail_id( $post_id );
		$title     = get_the_title( $post_id );

		$project_url = $link ? $link : ( $android ? $android : $ios );
		$quote_url   = ! empty( $settings['quote_link']['url'] ) ? $settings['quote_link']['url'] : '/أتصل-بنا/';
		if ( is_string( $quote_url ) && str_starts_with( $quote_url, '/' ) && ! str_starts_with( $quote_url, '//' ) ) {
			$quote_url = home_url( $quote_url );
		}

		$industry = '';
		$is_app   = false;
		$terms    = get_the_terms( $post_id, 'portfolio-categories' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$industry = $terms[0]->name;
			foreach ( $terms as $term ) {
				$slug = urldecode( (string) $term->slug );
				if ( 'تطبيقات-الهاتف' === $slug || false !== mb_strpos( (string) $term->name, 'تطبيق' ) ) {
					$is_app = true;
					break;
				}
			}
		}

		$service = $this->clean_service_label( $service, $title );

		$year  = get_the_date( 'Y', $post_id );
		$month = get_the_date( 'F Y', $post_id );
		$facts = array();
		if ( $client ) {
			$facts[] = array( 'العميل', $client );
		}
		if ( $month ) {
			$facts[] = array( 'التاريخ', $month );
		}
		if ( $industry ) {
			$facts[] = array( 'المجال', $industry );
		}
		if ( $service ) {
			$facts[] = array( 'المنصة', $service );
		} elseif ( $is_app ) {
			$facts[] = array( 'المنصة', 'تطبيق جوال' );
		} elseif ( $year ) {
			$facts[] = array( 'السنة', $year );
		}

		$device_id = ( $is_app && $thumb_id ) ? $thumb_id : ( $banner_id ? $banner_id : $thumb_id );
		$lead     = $this->first_non_empty_excerpt( array( $idea, $challenge, $journey ), 42 );

		$contact_url = home_url( '/أتصل-بنا/' );
		$mod         = $is_app ? ' qeema-cs-hero--app' : ' qeema-cs-hero--web';
		?>
		<section class="qeema-cs-hero<?php echo esc_attr( $mod ); ?>" id="qeema-cs-top">
			<div class="qeema-cs-hero__stage">
				<div class="qeema-cs-hero__glow" aria-hidden="true"></div>
				<div class="qeema-cs-hero__copy qeema-reveal">
					<?php if ( $industry || $service ) : ?>
						<span class="qeema-cs-chip"><?php echo esc_html( $industry ? $industry : $service ); ?></span>
					<?php endif; ?>

					<h1 class="qeema-cs-hero__title"><?php the_title(); ?></h1>

					<?php if ( $lead ) : ?>
						<p class="qeema-cs-hero__lead"><?php echo esc_html( $lead ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $facts ) ) : ?>
						<dl class="qeema-cs-facts">
							<?php foreach ( $facts as $i => $fact ) : ?>
								<div class="qeema-cs-facts__item<?php echo ( 3 === $i ) ? ' is-accent' : ''; ?>">
									<dt><?php echo esc_html( $fact[0] ); ?></dt>
									<dd><?php echo esc_html( $fact[1] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>

					<div class="qeema-cs-hero__ctas">
						<a class="qeema-cs-btn qeema-cs-btn--solid" href="<?php echo esc_url( $quote_url ? $quote_url : $contact_url ); ?>">ابدأ مشروعك</a>
						<a class="qeema-cs-btn qeema-cs-btn--ghost" href="#qeema-cs-screens">استكشف واجهات المنتج</a>
						<?php if ( $project_url ) : ?>
							<a class="qeema-cs-btn qeema-cs-btn--ghost" href="<?php echo esc_url( $project_url ); ?>" target="_blank" rel="noopener">
								<?php echo $is_app ? 'فتح التطبيق' : 'زيارة الموقع'; ?>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( $device_id ) : ?>
					<div class="qeema-cs-hero__visual qeema-reveal" style="--reveal-delay:.12s">
						<?php if ( $is_app ) : ?>
							<figure class="qeema-cs-shot">
								<?php
								echo wp_get_attachment_image( $device_id, 'large', false, array(
									'alt'           => get_the_title( $post_id ),
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'decoding'      => 'async',
									'sizes'         => '(max-width:980px) 70vw, 320px',
								) );
								?>
							</figure>
						<?php else : ?>
							<figure class="qeema-cs-webframe">
								<div class="qeema-cs-webframe__chrome" aria-hidden="true"><span></span><span></span><span></span></div>
								<div class="qeema-cs-webframe__screen">
									<?php
									echo wp_get_attachment_image( $device_id, 'large', false, array(
										'alt'           => get_the_title( $post_id ),
										'loading'       => 'eager',
										'fetchpriority' => 'high',
										'decoding'      => 'async',
									) );
									?>
								</div>
							</figure>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Some imported ACF "service" values were glued to the title/body.
	 */
	private function clean_service_label( $service, $title ) {
		if ( ! is_string( $service ) || '' === trim( $service ) ) {
			return '';
		}
		$text = trim( wp_strip_all_tags( $service ) );
		if ( $title ) {
			$pos = mb_strpos( $text, $title );
			if ( false !== $pos && $pos > 8 ) {
				$text = trim( mb_substr( $text, 0, $pos ) );
			}
		}
		return wp_trim_words( $text, 14, '…' );
	}

	private function first_non_empty_excerpt( array $candidates, $words = 40 ) {
		foreach ( $candidates as $value ) {
			if ( ! is_string( $value ) || '' === trim( wp_strip_all_tags( $value ) ) ) {
				continue;
			}
			return wp_trim_words( wp_strip_all_tags( $value ), $words, '…' );
		}
		return '';
	}
}
