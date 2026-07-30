<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reusable stats/counter bar. Consolidates two near-duplicate designs found
 * on production (qt-stats-clean on the homepage, qt-stats-pro on service
 * pages) into one widget — same repeater, same count-up animation, used
 * wherever a stats row is needed instead of rebuilding it per page.
 */
class Qeema_Stats_Counter_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-stats-counter';
	}

	public function get_title() {
		return __( 'Stats Counter', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-counter';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-stats-counter' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Stats', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '4',
			'options' => array(
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
		) );

		$this->add_control( 'stats', array(
			'label'       => __( 'Stats', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'target',
					'label'   => __( 'Number (target to count up to)', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::NUMBER,
					'default' => 100,
				),
				array(
					'name'    => 'prefix',
					'label'   => __( 'Prefix', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '+',
				),
				array(
					'name'    => 'suffix',
					'label'   => __( 'Suffix', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
				array(
					'name'    => 'static_text',
					'label'   => __( 'Static text instead (e.g. "24/7") — leave blank to use the number above', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
				array(
					'name'    => 'label',
					'label'   => __( 'Label', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'مشروع',
				),
			),
			'default'     => array(
				array( 'target' => 6500, 'prefix' => '+', 'label' => 'مشروع' ),
				array( 'static_text' => '24/7', 'label' => 'دعم مستمر' ),
				array( 'target' => 99, 'suffix' => '%', 'label' => 'جاهزية' ),
				array( 'target' => 80, 'prefix' => '+', 'label' => 'مهندس' ),
			),
			'title_field' => '{{{ label }}}',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'background_section', array(
			'label' => __( 'Background', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'background_color', array(
			'label' => __( 'Background Color', 'qeematech-elementor-widgets' ),
			'type'  => \Elementor\Controls_Manager::COLOR,
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$style    = ! empty( $settings['background_color'] ) ? 'background-color:' . esc_attr( $settings['background_color'] ) . ';' : '';
		?>
		<section class="qeema-stats-counter" <?php echo $style ? 'style="' . $style . '"' : ''; ?>>
			<div class="qeema-stats-counter__wrap" style="--qeema-stats-cols:<?php echo esc_attr( $settings['columns'] ); ?>;">
				<?php foreach ( $settings['stats'] as $stat ) : ?>
					<div class="qeema-stat-box">
						<?php if ( ! empty( $stat['static_text'] ) ) : ?>
							<div class="qeema-stat-box__num"><?php echo esc_html( $stat['static_text'] ); ?></div>
						<?php else : ?>
							<div class="qeema-stat-box__num"
								data-target="<?php echo esc_attr( $stat['target'] ); ?>"
								data-prefix="<?php echo esc_attr( $stat['prefix'] ); ?>"
								data-suffix="<?php echo esc_attr( $stat['suffix'] ); ?>">0</div>
						<?php endif; ?>
						<div class="qeema-stat-box__label"><?php echo esc_html( $stat['label'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
