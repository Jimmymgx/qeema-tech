<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Numbered "why choose us" timeline — a connecting line strung through 4
 * large-number step cards. Deliberately a different visual pattern from
 * Feature/Service Grid (icon cards) so pages that need both (e.g. About Us)
 * don't end up looking like two copies of the same section.
 */
class Qeema_Why_Us_Steps_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-why-us-steps';
	}

	public function get_title() {
		return __( 'Why Us — Numbered Steps', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-number-field';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-scroll-reveal' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'header_section', array(
			'label' => __( 'Header', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'badge', array(
			'label'   => __( 'Badge', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'لماذا قيمة تك',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'نمنح مشروعك أساسًا أقوى للنجاح',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'steps_section', array(
			'label' => __( 'Steps', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'steps', array(
			'label'       => __( 'Steps', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'title',
					'label'   => __( 'Title', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'Step title',
				),
				array(
					'name'    => 'description',
					'label'   => __( 'Description', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
				),
				array(
					'name'    => 'icon_text',
					'label'   => __( 'Icon (Font Awesome class e.g. "fas fa-search") — grid layout only', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
			),
			'default'     => array(),
			'title_field' => '{{{ title }}}',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'layout_section', array(
			'label' => __( 'Layout', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'layout_mode', array(
			'label'   => __( 'Layout', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'grid',
			'options' => array(
				'grid'  => __( 'Grid (numbered cards + connector)', 'qeematech-elementor-widgets' ),
				'split' => __( 'Split (heading beside a stacked list)', 'qeematech-elementor-widgets' ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$is_split = 'split' === $settings['layout_mode'];
		?>
		<section class="qeema-why-steps<?php echo $is_split ? ' qeema-why-steps--split' : ''; ?>">
			<?php if ( $is_split ) : ?>
				<div class="qeema-why-steps__split-wrap">
					<div class="qeema-why-steps__head">
						<?php if ( ! empty( $settings['badge'] ) ) : ?>
							<span class="qeema-why-steps__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
						<?php endif; ?>
						<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
						<?php if ( ! empty( $settings['subheading'] ) ) : ?>
							<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
						<?php endif; ?>
					</div>

					<div class="qeema-why-steps__split-steps">
						<?php foreach ( $settings['steps'] as $index => $step ) : ?>
							<article class="qeema-why-step qeema-why-step--row qeema-reveal" style="--reveal-delay:<?php echo esc_attr( $index * 0.1 ); ?>s;">
								<div class="qeema-why-step__num"><?php echo esc_html( str_pad( $index + 1, 2, '0', STR_PAD_LEFT ) ); ?></div>
								<div>
									<h3><?php echo esc_html( $step['title'] ); ?></h3>
									<?php if ( ! empty( $step['description'] ) ) : ?>
										<p><?php echo esc_html( $step['description'] ); ?></p>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="qeema-why-steps__wrap">
					<div class="qeema-why-steps__head">
						<?php if ( ! empty( $settings['badge'] ) ) : ?>
							<span class="qeema-why-steps__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
						<?php endif; ?>
						<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
						<?php if ( ! empty( $settings['subheading'] ) ) : ?>
							<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
						<?php endif; ?>
					</div>

					<div class="qeema-why-steps__grid">
						<?php foreach ( $settings['steps'] as $index => $step ) :
							$num = str_pad( $index + 1, 2, '0', STR_PAD_LEFT );
							?>
							<article class="qeema-why-step qeema-reveal" style="--reveal-delay:<?php echo esc_attr( $index * 0.1 ); ?>s;">
								<span class="qeema-why-step__ghost" aria-hidden="true"><?php echo esc_html( $num ); ?></span>
								<div class="qeema-why-step__num">
									<?php if ( ! empty( $step['icon_text'] ) ) : ?>
										<i class="<?php echo esc_attr( $step['icon_text'] ); ?>"></i>
									<?php else : ?>
										<?php echo esc_html( $num ); ?>
									<?php endif; ?>
								</div>
								<span class="qeema-why-step__index"><?php echo esc_html( $num ); ?></span>
								<h3><?php echo esc_html( $step['title'] ); ?></h3>
								<?php if ( ! empty( $step['description'] ) ) : ?>
									<p><?php echo esc_html( $step['description'] ); ?></p>
								<?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}
}
