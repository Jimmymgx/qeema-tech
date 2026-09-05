<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Narrative body of the single-portfolio case-study template. Maps directly
 * onto the real ACF fields every `portfolio` post already carries — idea /
 * التحدي / الحل_من_قيمة_تك (+ optional الحل_الاول..السابع bullets) /
 * idea_copy2 — reading the CURRENT post at render time since this widget is
 * attached via an Elementor Theme Builder condition, not hand-placed.
 *
 * Deliberately skips the `result_copy2/3/4/5` fields — flagged during
 * research as pre-existing duplicate cruft on the data, not a real repeater;
 * only the primary `result` field is shown. A section with no real content
 * on a given post simply doesn't render, matching the "never fabricate"
 * rule already established for every other import in this project.
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

		$sections = $this->build_sections( $post_id );
		if ( empty( $sections ) ) {
			return;
		}
		?>
		<section class="qeema-portfolio-case-story">
			<div class="qeema-portfolio-case-story__wrap">
				<div class="qeema-portfolio-case-story__spine" aria-hidden="true"></div>
				<?php foreach ( $sections as $i => $section ) : ?>
					<div class="qeema-portfolio-case-story__section qeema-portfolio-case-story__section--<?php echo esc_attr( $section['mod'] ); ?> qeema-reveal" style="--reveal-delay:<?php echo esc_attr( $i * .08 ); ?>s">
						<div class="qeema-portfolio-case-story__marker">
							<span class="qeema-portfolio-case-story__ghost"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
							<span class="qeema-portfolio-case-story__label"><?php echo esc_html( $section['label'] ); ?></span>
							<h3 class="qeema-portfolio-case-story__heading">
								<span class="qeema-portfolio-case-story__dot"></span><?php echo esc_html( $section['heading'] ); ?>
							</h3>
						</div>
						<div class="qeema-portfolio-case-story__body">
							<?php echo wp_kses_post( $section['content'] ); ?>
						</div>
					</div>
				<?php endforeach; ?>

				<?php $this->render_gallery( $post_id ); ?>
			</div>
		</section>
		<?php
	}

	private function build_sections( $post_id ) {
		$sections = array();

		$idea = get_field( 'idea', $post_id );
		if ( $idea ) {
			$sections[] = array(
				'label'   => 'الفكرة',
				'heading' => 'فكرة المشروع',
				'content' => $idea,
				'mod'     => 'idea',
			);
		}

		$challenge = get_field( 'التحدي', $post_id );
		if ( $challenge ) {
			$sections[] = array(
				'label'   => 'التحدي',
				'heading' => 'التحدي',
				'content' => '<p>' . esc_html( $challenge ) . '</p>',
				'mod'     => 'challenge',
			);
		}

		$solution = get_field( 'الحل_من_قيمة_تك', $post_id );
		$bullets  = array();
		foreach ( array( 'الحل_الاول', 'الحل_الثاني', 'الحل_الثالث', 'الحل_الرابع', 'الحل_الخامس', 'الحل_السادس', 'الحل_السابع' ) as $field ) {
			$value = get_field( $field, $post_id );
			if ( $value ) {
				$bullets[] = $value;
			}
		}
		if ( $solution || $bullets ) {
			$content = $solution ? '<p>' . esc_html( $solution ) . '</p>' : '';
			if ( $bullets ) {
				$content .= '<ul class="qeema-portfolio-case-story__bullets">';
				foreach ( $bullets as $bullet ) {
					$content .= '<li>' . esc_html( $bullet ) . '</li>';
				}
				$content .= '</ul>';
			}
			$result = get_field( 'result', $post_id );
			if ( $result ) {
				$content .= '<div class="qeema-portfolio-case-story__result">' . esc_html( $result ) . '</div>';
			}
			$sections[] = array(
				'label'   => 'آلية التنفيذ',
				'heading' => 'الحل من قيمة تك',
				'content' => $content,
				'mod'     => 'approach',
			);
		}

		$journey = get_field( 'idea_copy2', $post_id );
		if ( $journey ) {
			$sections[] = array(
				'label'   => 'رحلة العميل',
				'heading' => 'رحلة العميل معنا',
				'content' => $journey,
				'mod'     => 'journey',
			);
		}

		return $sections;
	}

	private function render_gallery( $post_id ) {
		$gallery = get_field( 'gallery', $post_id );
		if ( empty( $gallery ) || ! is_array( $gallery ) ) {
			return;
		}
		?>
		<div class="qeema-portfolio-case-story__gallery qeema-reveal">
			<?php foreach ( $gallery as $image ) :
				$image_id = is_array( $image ) ? ( $image['ID'] ?? 0 ) : $image;
				if ( ! $image_id ) {
					continue;
				}
				?>
				<?php echo wp_get_attachment_image( $image_id, 'medium_large', false, array( 'loading' => 'lazy' ) ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
