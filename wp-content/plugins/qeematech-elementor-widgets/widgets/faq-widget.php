<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ accordion — purpose-built rather than reusing Elementor core's native
 * Accordion widget (which was the previous approach on the Services page).
 * That widget's markup/JS is closed-box: its header row is a separate
 * Heading/Text-Editor widget stack (inconsistent spacing vs. every other
 * section's single-widget badge+h2+p head) and its open/close is a plain
 * show/hide with no transition. This widget matches the same
 * badge/heading/subheading head markup used by Why-Us-Steps/Value
 * Cards/etc. exactly, drops the +/- icon in favor of the numbered chip
 * alone, and animates open/close with a CSS grid-rows transition (see
 * faq-accordion.js) instead of a hard cut.
 */
class Qeema_Faq_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-faq';
	}

	public function get_title() {
		return __( 'FAQ Accordion', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-help-o';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-faq-accordion', 'qeema-scroll-reveal' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'header_section', array(
			'label' => __( 'Header', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'badge', array(
			'label'   => __( 'Badge', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'الأسئلة الشائعة',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'عندك سؤال عن خدماتنا؟',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'items_section', array(
			'label' => __( 'Questions', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'items', array(
			'label'       => __( 'Items', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'question',
					'label'   => __( 'Question', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'Question',
				),
				array(
					'name'    => 'answer',
					'label'   => __( 'Answer', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
				),
			),
			'default'     => array(),
			'title_field' => '{{{ question }}}',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'seo_section', array(
			'label' => __( 'SEO', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'faq_schema', array(
			'label'        => __( 'Output FAQPage schema', 'qeematech-elementor-widgets' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'label_on'     => __( 'Yes', 'qeematech-elementor-widgets' ),
			'label_off'    => __( 'No', 'qeematech-elementor-widgets' ),
			'return_value' => 'yes',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$widget_id  = $this->get_id();
		?>
		<section class="qeema-faq">
			<div class="qeema-faq__glow qeema-faq__glow--a"></div>
			<div class="qeema-faq__glow qeema-faq__glow--b"></div>
			<div class="qeema-faq__wrap">
				<div class="qeema-faq__head">
					<?php if ( ! empty( $settings['badge'] ) ) : ?>
						<span class="qeema-faq__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
					<?php endif; ?>
					<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="qeema-faq__grid">
					<?php foreach ( $settings['items'] as $index => $item ) :
						$num       = str_pad( $index + 1, 2, '0', STR_PAD_LEFT );
						$panel_id  = 'qeema-faq-a-' . $widget_id . '-' . $index;
						$delay     = esc_attr( ( $index % 2 ) * 0.12 );
						?>
						<div class="qeema-faq-item qeema-reveal" style="--reveal-delay:<?php echo $delay; ?>s;">
							<button type="button" class="qeema-faq-item__q" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
								<span class="qeema-faq-item__num"><?php echo esc_html( $num ); ?></span>
								<span class="qeema-faq-item__question"><?php echo esc_html( $item['question'] ); ?></span>
							</button>
							<div class="qeema-faq-item__a" id="<?php echo esc_attr( $panel_id ); ?>">
								<div class="qeema-faq-item__a-inner">
									<p><?php echo wp_kses_post( $item['answer'] ); ?></p>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php

		if ( 'yes' === $settings['faq_schema'] && ! empty( $settings['items'] ) ) {
			$json = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => array(),
			);
			foreach ( $settings['items'] as $item ) {
				$json['mainEntity'][] = array(
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( $item['question'] ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $item['answer'] ),
					),
				);
			}
			echo '<script type="application/ld+json">' . wp_json_encode( $json, JSON_UNESCAPED_UNICODE ) . '</script>';
		}
	}
}
