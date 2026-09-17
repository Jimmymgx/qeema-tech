<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Case-study body matching the approved mockup sections: story, challenge/
 * solution duet, capabilities grid, impact results, journey, visual screens,
 * and final CTA. Content is always sourced from real ACF fields.
 */
class Qeema_Portfolio_Case_Story_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-portfolio-case-story';
	}

	public function get_title() {
		return __( 'Portfolio Case Story', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-scroll-reveal' );
	}

	protected function register_controls() {}

	protected function render() {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$idea      = get_field( 'idea', $post_id );
		$challenge = get_field( 'التحدي', $post_id );
		$solution  = get_field( 'الحل_من_قيمة_تك', $post_id );
		$journey   = get_field( 'idea_copy2', $post_id );
		$features  = $this->collect_features( $post_id );
		$results   = $this->collect_results( $post_id );
		$is_app    = $this->is_app( $post_id );

		$story_source = '';
		$story        = '';
		if ( is_string( $idea ) && '' !== trim( wp_strip_all_tags( $idea ) ) ) {
			$story        = $idea;
			$story_source = 'idea';
		} elseif ( is_string( $journey ) && '' !== trim( wp_strip_all_tags( $journey ) ) ) {
			$story        = $journey;
			$story_source = 'journey';
		} elseif ( is_string( $challenge ) && '' !== trim( wp_strip_all_tags( $challenge ) ) ) {
			$story        = $challenge;
			$story_source = 'challenge';
		}

		if ( ! ( $story || $challenge || $solution || $features || $results || get_field( 'gallery', $post_id ) || get_post_thumbnail_id( $post_id ) ) ) {
			return;
		}

		$contact = home_url( '/أتصل-بنا/' );
		$works   = home_url( '/أعمالنا/' );
		?>
		<section class="qeema-cs-story">

			<?php if ( $story ) : ?>
				<section class="qeema-cs-band qeema-cs-band--story">
					<div class="qeema-cs-wrap">
						<p class="qeema-cs-label">نظرة عامة</p>
						<div class="qeema-cs-story-grid qeema-reveal">
							<div class="qeema-cs-story-main">
								<div class="qeema-cs-story-lead"><?php echo wp_kses_post( wpautop( $story ) ); ?></div>
								<ul class="qeema-cs-checks">
									<?php foreach ( $this->story_checks( $is_app ) as $check ) : ?>
										<li><span aria-hidden="true">✓</span><?php echo esc_html( $check ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
							<aside class="qeema-cs-glass qeema-cs-aside">
								<p class="qeema-cs-aside__kicker">الهدف من المنتج</p>
								<h3><?php echo $is_app ? 'تجربة تطبيق واضحة وموثوقة' : 'تجربة رقمية واضحة وقابلة للنمو'; ?></h3>
								<p><?php echo esc_html( $this->aside_blurb( $solution, $is_app ) ); ?></p>
								<div class="qeema-cs-aside__foot">
									<span><?php echo $is_app ? 'تطبيقات الهاتف' : 'حلول رقمية'; ?></span>
									<strong>Qeema Tech</strong>
								</div>
							</aside>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $challenge || $solution ) : ?>
				<section class="qeema-cs-band">
					<div class="qeema-cs-wrap qeema-cs-duet qeema-reveal">
						<?php if ( $challenge ) : ?>
							<article class="qeema-cs-glass qeema-cs-panel qeema-cs-panel--challenge">
								<div class="qeema-cs-panel__top">
									<span class="qeema-cs-tag qeema-cs-tag--rose">التحدي</span>
									<span class="qeema-cs-panel__ico" aria-hidden="true">⚠</span>
								</div>
								<h2>ما الذي كان يجب حله؟</h2>
								<p><?php echo esc_html( $challenge ); ?></p>
							</article>
						<?php endif; ?>
						<?php if ( $solution ) : ?>
							<article class="qeema-cs-glass qeema-cs-panel qeema-cs-panel--solution">
								<div class="qeema-cs-panel__top">
									<span class="qeema-cs-tag qeema-cs-tag--cyan">الحل</span>
									<span class="qeema-cs-panel__ico" aria-hidden="true">⚡</span>
								</div>
								<h2>كيف بنته قيمة تك؟</h2>
								<p><?php echo esc_html( $solution ); ?></p>
							</article>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $features ) ) : ?>
				<section class="qeema-cs-band qeema-cs-band--dark">
					<div class="qeema-cs-wrap qeema-reveal">
						<header class="qeema-cs-head">
							<p class="qeema-cs-label">عن المنتج</p>
							<h2>قدرات المنتج</h2>
						</header>
						<ol class="qeema-cs-features">
							<?php
							$total = count( $features );
							foreach ( $features as $n => $feature ) :
								$parts = $this->split_feature( $feature );
								$wide  = ( $n === $total - 1 && 0 !== $total % 3 );
								?>
								<li class="qeema-cs-glass qeema-cs-feature<?php echo $wide ? ' is-wide' : ''; ?>">
									<span class="qeema-cs-feature__num"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span>
									<?php if ( $parts['title'] ) : ?>
										<h3 class="qeema-cs-feature__title"><?php echo esc_html( $parts['title'] ); ?></h3>
									<?php endif; ?>
									<p><?php echo esc_html( $parts['body'] ); ?></p>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $results ) ) : ?>
				<section class="qeema-cs-band">
					<div class="qeema-cs-wrap qeema-reveal">
						<header class="qeema-cs-head">
							<p class="qeema-cs-label">النتائج</p>
							<h2>نتائج ملموسة</h2>
						</header>
						<div class="qeema-cs-results">
							<?php foreach ( $results as $result ) : ?>
								<blockquote class="qeema-cs-glass qeema-cs-result">
									<p><?php echo esc_html( $result ); ?></p>
								</blockquote>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $journey && 'journey' !== $story_source ) : ?>
				<section class="qeema-cs-band">
					<div class="qeema-cs-wrap">
						<div class="qeema-cs-glass qeema-cs-journey qeema-reveal">
							<p class="qeema-cs-label">رحلة التنفيذ</p>
							<div class="qeema-cs-journey__text"><?php echo wp_kses_post( wpautop( $journey ) ); ?></div>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php $this->render_screens( $post_id, $is_app ); ?>

			<section class="qeema-cs-band" id="qeema-cs-contact">
				<div class="qeema-cs-wrap">
					<div class="qeema-cs-glass qeema-cs-cta qeema-reveal">
						<div class="qeema-cs-cta__copy">
							<p class="qeema-cs-label">الخطوة التالية</p>
							<h2>جاهز لمشروع بنفس المستوى؟</h2>
							<p>نحوّل فكرتك إلى منتج رقمي قابل للنمو — من التصميم حتى الإطلاق.</p>
						</div>
						<div class="qeema-cs-cta__actions">
							<a class="qeema-cs-btn qeema-cs-btn--solid" href="<?php echo esc_url( $contact ); ?>">ابدأ مشروعك</a>
							<a class="qeema-cs-btn qeema-cs-btn--ghost" href="<?php echo esc_url( $works ); ?>">شاهد أعمالنا</a>
						</div>
					</div>
				</div>
			</section>
		</section>
		<?php
	}

	private function is_app( $post_id ) {
		$terms = get_the_terms( $post_id, 'portfolio-categories' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return false;
		}
		foreach ( $terms as $term ) {
			$slug = urldecode( (string) $term->slug );
			if ( 'تطبيقات-الهاتف' === $slug || false !== mb_strpos( (string) $term->name, 'تطبيق' ) ) {
				return true;
			}
		}
		return false;
	}

	private function aside_blurb( $solution, $is_app ) {
		if ( is_string( $solution ) && '' !== trim( wp_strip_all_tags( $solution ) ) ) {
			return wp_trim_words( wp_strip_all_tags( $solution ), 36, '…' );
		}
		return $is_app
			? 'بنينا تجربة استخدام سلسة تربط الفكرة بالتنفيذ، مع واجهة واضحة ومتابعة لحظية تعزّز ثقة المستخدم.'
			: 'بنينا منتجًا رقميًا واضح الهوية، بتجربة استخدام سلسة وتنفيذ يليق بطموح المشروع.';
	}

	private function story_checks( $is_app ) {
		return $is_app
			? array( 'واجهة استخدام بسيطة', 'ثقة وتوثيق كامل', 'متابعة لحظية', 'تجربة سلسة لكل الأعمار' )
			: array( 'هوية بصرية احترافية', 'هيكل معلوماتي واضح', 'تجربة استخدام سلسة', 'جاهزية للإطلاق والنمو' );
	}

	private function split_feature( $feature ) {
		$text = trim( wp_strip_all_tags( (string) $feature ) );
		if ( '' === $text ) {
			return array( 'title' => '', 'body' => '' );
		}
		if ( preg_match( '/^(.{8,42}?)[:：\-–—]\s*(.+)$/u', $text, $m ) ) {
			return array( 'title' => trim( $m[1] ), 'body' => trim( $m[2] ) );
		}
		$words = preg_split( '/\s+/u', $text );
		if ( count( $words ) > 10 ) {
			$title = implode( ' ', array_slice( $words, 0, 6 ) );
			$body  = implode( ' ', array_slice( $words, 6 ) );
			return array( 'title' => $title, 'body' => $body );
		}
		return array( 'title' => '', 'body' => $text );
	}

	private function collect_features( $post_id ) {
		$out = array();
		foreach ( array( 'الحل_الاول', 'الحل_الثاني', 'الحل_الثالث', 'الحل_الرابع', 'الحل_الخامس', 'الحل_السادس', 'الحل_السابع' ) as $field ) {
			$value = get_field( $field, $post_id );
			if ( $value ) {
				$out[] = $value;
			}
		}
		if ( ! empty( $out ) ) {
			return $out;
		}
		// Fallback for projects that only store narrative ACF fields.
		$pool = array();
		foreach ( array( 'الحل_من_قيمة_تك', 'idea', 'التحدي' ) as $field ) {
			$value = get_field( $field, $post_id );
			if ( is_string( $value ) && $value ) {
				$pool[] = wp_strip_all_tags( $value );
			}
		}
		$chunks = preg_split( '/(?<=[\.\!\?؟。])\s+/u', implode( ' ', $pool ) );
		foreach ( $chunks as $chunk ) {
			$chunk = trim( $chunk );
			if ( mb_strlen( $chunk ) < 28 ) {
				continue;
			}
			$out[] = $chunk;
			if ( count( $out ) >= 6 ) {
				break;
			}
		}
		return $out;
	}

	private function collect_results( $post_id ) {
		$raw = array();
		foreach ( array( 'result', 'result_copy', 'result_copy2', 'result_copy3', 'result_copy4', 'result_5' ) as $field ) {
			$value = get_field( $field, $post_id );
			if ( is_string( $value ) && '' !== trim( wp_strip_all_tags( $value ) ) ) {
				$raw[] = trim( wp_strip_all_tags( $value ) );
			}
		}
		$skip = array();
		foreach ( array( 'idea', 'idea_copy2', 'التحدي', 'الحل_من_قيمة_تك' ) as $field ) {
			$value = get_field( $field, $post_id );
			if ( is_string( $value ) && $value ) {
				$skip[] = md5( mb_strtolower( preg_replace( '/\s+/u', ' ', trim( wp_strip_all_tags( $value ) ) ) ) );
			}
		}
		$unique = array();
		$seen   = array();
		foreach ( $raw as $text ) {
			$key = md5( mb_strtolower( preg_replace( '/\s+/u', ' ', $text ) ) );
			if ( isset( $seen[ $key ] ) || in_array( $key, $skip, true ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$unique[]     = $text;
		}
		return $unique;
	}

	private function render_screens( $post_id, $is_app ) {
		$images  = array();
		$gallery = get_field( 'gallery', $post_id );
		if ( ! empty( $gallery ) && is_array( $gallery ) ) {
			foreach ( $gallery as $image ) {
				$id = is_array( $image ) ? (int) ( $image['ID'] ?? 0 ) : (int) $image;
				if ( $id ) {
					$images[] = $id;
				}
			}
		}
		$thumb  = (int) get_post_thumbnail_id( $post_id );
		$banner = (int) get_field( 'banner', $post_id );
		if ( $thumb && ! in_array( $thumb, $images, true ) ) {
			array_unshift( $images, $thumb );
		}
		if ( $banner && ! in_array( $banner, $images, true ) ) {
			$images[] = $banner;
		}
		$images = array_values( array_unique( array_filter( $images ) ) );
		if ( empty( $images ) ) {
			return;
		}
		/* Keep the showcase tight like the approved mockup (4 device frames). */
		$images = array_slice( $images, 0, 4 );

		/* Generic, non-project-specific captions cycling by image position — applies
		 * identically across all portfolio posts sharing this template, so wording
		 * must stay sensible for any project rather than describing a specific one. */
		$captions = $is_app
			? array( 'الشاشة الرئيسية', 'تجربة الاستخدام', 'لوحة التحكم', 'متابعة الأداء' )
			: array( 'الصفحة الرئيسية', 'تفاصيل الخدمة', 'لوحة التحكم', 'تجربة الموبايل' );
		?>
		<section class="qeema-cs-band qeema-cs-band--screens" id="qeema-cs-screens">
			<div class="qeema-cs-wrap qeema-reveal">
				<header class="qeema-cs-head">
					<p class="qeema-cs-label">الهدف التصميمي</p>
					<h2>من داخل المنتج</h2>
				</header>
				<div class="qeema-cs-screens <?php echo $is_app ? 'is-app' : 'is-web'; ?>">
					<?php foreach ( $images as $i => $image_id ) : ?>
						<figure class="qeema-cs-screen<?php echo ( 0 === $i ) ? ' is-featured' : ''; ?>">
							<?php if ( $is_app ) : ?>
								<div class="qeema-cs-shot qeema-cs-shot--sm">
									<?php
									echo wp_get_attachment_image( $image_id, 'full', false, array(
										'loading'  => 'lazy',
										'decoding' => 'async',
										'sizes'    => '(max-width:640px) 60vw, 220px',
									) );
									?>
								</div>
							<?php else : ?>
								<div class="qeema-cs-webframe">
									<div class="qeema-cs-webframe__chrome" aria-hidden="true"><span></span><span></span><span></span></div>
									<div class="qeema-cs-webframe__screen">
										<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
									</div>
								</div>
							<?php endif; ?>
							<?php if ( isset( $captions[ $i ] ) ) : ?>
								<figcaption class="qeema-cs-shot__caption"><?php echo esc_html( $captions[ $i ] ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
