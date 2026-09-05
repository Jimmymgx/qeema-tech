<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Client-logos trust strip — reads the "Our Clients" ACF options page
 * (menu_slug `ourclient`, field `client` repeater with a `logo` image
 * sub-field) that was already registered in this codebase but had no widget
 * rendering it anywhere. Renders an infinite CSS marquee once there are
 * enough logos to loop smoothly; falls back to a static row for a handful of
 * logos, and renders nothing at all if the options page is still empty.
 */
class Qeema_Trusted_By_Widget extends \Elementor\Widget_Base {

	const MARQUEE_MIN_LOGOS = 6;

	public function get_name() {
		return 'qeema-trusted-by';
	}

	public function get_title() {
		return __( 'Trusted By (client logos)', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-media-carousel';
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

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'موثوق من قبل',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'شركات وعلامات تجارية اختارت قيمة تك',
		) );

		$this->add_control( 'speed', array(
			'label'   => __( 'Marquee speed (seconds per loop)', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 32,
		) );

		$this->add_control( 'layout', array(
			'label'   => __( 'Layout', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'single',
			'options' => array(
				'single' => __( 'Single row (classic)', 'qeematech-elementor-widgets' ),
				'triple' => __( 'Multi-row wall', 'qeematech-elementor-widgets' ),
				'grid'   => __( 'Creative Wall (animated grid)', 'qeematech-elementor-widgets' ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! function_exists( 'get_field' ) ) {
			return;
		}

		$settings = $this->get_settings_for_display();
		$clients  = get_field( 'client', 'option' );

		$logo_ids = array();
		if ( ! empty( $clients ) && is_array( $clients ) ) {
			foreach ( $clients as $row ) {
				if ( ! empty( $row['logo'] ) ) {
					$logo_ids[] = (int) $row['logo'];
				}
			}
		}

		if ( empty( $logo_ids ) ) {
			return;
		}

		$is_grid     = 'grid' === $settings['layout'];
		$use_marquee = ! $is_grid && count( $logo_ids ) >= self::MARQUEE_MIN_LOGOS;
		$wrap_class  = 'qeema-trusted-by qeema-reveal' . ( ( $use_marquee || $is_grid ) ? '' : ' qeema-trusted-by--static' );
		?>
		<section class="<?php echo esc_attr( $wrap_class ); ?>">
			<div class="qeema-trusted-by__head">
				<?php if ( ! empty( $settings['eyebrow'] ) ) : ?>
					<span class="qeema-trusted-by__eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $settings['heading'] ) ) : ?>
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
				<?php endif; ?>
			</div>

			<?php if ( $is_grid ) : ?>
				<?php echo $this->render_grid( $logo_ids ); ?>
			<?php elseif ( $use_marquee && 'triple' === $settings['layout'] ) : ?>
				<?php echo $this->render_triple_rows( $logo_ids, (int) $settings['speed'] ); ?>
			<?php elseif ( $use_marquee ) : ?>
				<div class="qeema-trusted-by__viewport">
					<div class="qeema-trusted-by__track" style="--speed:<?php echo esc_attr( (int) $settings['speed'] ); ?>s;">
						<?php echo $this->render_logos( $logo_ids ); ?>
						<?php echo $this->render_logos( $logo_ids, true ); ?>
					</div>
				</div>
			<?php else : ?>
				<div class="qeema-trusted-by__static-row">
					<?php echo $this->render_logos( $logo_ids ); ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * "Trust wall" layout: every logo as its own glass tile in a responsive
	 * grid instead of a scrolling strip, so the full client roster reads at a
	 * glance on a dedicated clients page. Each tile fades/rises in with a
	 * per-tile stagger (driven by the `--i` custom property set inline, since
	 * this project's existing `.qeema-reveal`/`is-visible` scroll-trigger only
	 * toggles one class on the section — no new JS needed), showing each
	 * brand's logo in full color and lifting on hover.
	 */
	private function render_grid( $logo_ids ) {
		$out = '<div class="qeema-trusted-by__grid">';
		$i   = 0;
		foreach ( $logo_ids as $id ) {
			$img = wp_get_attachment_image( $id, 'medium', false, array( 'loading' => 'lazy' ) );
			if ( ! $img ) {
				continue;
			}
			$out .= '<span class="qeema-trusted-by__grid-item" style="--i:' . esc_attr( $i ) . '"><span class="qeema-trusted-by__grid-logo">' . $img . '</span></span>';
			++$i;
		}
		$out .= '</div>';
		return $out;
	}

	/**
	 * "Wall of trust" layout: same logos as the single-row marquee, but split
	 * across 2 independent tracks (opposite scroll directions, slightly
	 * different speeds) instead of one strip. Each row reuses the FULL logo
	 * set (rotated to a different starting point per row, purely for visual
	 * variety) rather than splitting logos in half, so every row is exactly
	 * as wide - and therefore exactly as safe from ever running out of
	 * content mid-loop - as the already-proven single-row marquee. Kept to 2
	 * rows (not 3) so logos can run larger without the section getting tall.
	 */
	private function render_triple_rows( $logo_ids, $base_speed ) {
		$count      = count( $logo_ids );
		$rotate_by  = max( 1, (int) floor( $count / 2 ) );
		$rows       = array(
			array( 'ids' => $logo_ids, 'reverse' => false, 'speed' => $base_speed ),
			array( 'ids' => $this->rotate_logos( $logo_ids, $rotate_by ), 'reverse' => true, 'speed' => (int) round( $base_speed * 1.2 ) ),
		);

		$out = '<div class="qeema-trusted-by__rows">';
		foreach ( $rows as $row ) {
			$track_class = 'qeema-trusted-by__track' . ( $row['reverse'] ? ' qeema-trusted-by__track--reverse' : '' );
			$out        .= '<div class="qeema-trusted-by__viewport">';
			// Direct animation-duration (not the --speed custom property the
			// single-row layout uses) so it isn't clobbered by the global
			// stylesheet's fixed-duration override meant for that legacy path.
			$out        .= '<div class="' . esc_attr( $track_class ) . '" style="animation-duration:' . esc_attr( $row['speed'] ) . 's;">';
			$out        .= $this->render_logos( $row['ids'] );
			$out        .= $this->render_logos( $row['ids'], true );
			$out        .= '</div></div>';
		}
		$out .= '</div>';
		return $out;
	}

	private function rotate_logos( $logo_ids, $offset ) {
		$offset = $offset % count( $logo_ids );
		return array_merge( array_slice( $logo_ids, $offset ), array_slice( $logo_ids, 0, $offset ) );
	}

	private function render_logos( $logo_ids, $is_duplicate = false ) {
		$out = $is_duplicate ? '<div class="qeema-trusted-by__track-dup" aria-hidden="true">' : '';
		foreach ( $logo_ids as $id ) {
			$img = wp_get_attachment_image( $id, 'medium', false, array( 'loading' => 'lazy' ) );
			if ( ! $img ) {
				continue;
			}
			$out .= '<span class="qeema-trusted-by__logo">' . $img . '</span>';
		}
		$out .= $is_duplicate ? '</div>' : '';
		return $out;
	}
}
