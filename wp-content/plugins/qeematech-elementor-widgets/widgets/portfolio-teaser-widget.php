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
			),
			'default' => array(),
			'title_field' => '{{{ label }}}',
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
						<?php foreach ( $settings['categories'] as $cat ) : ?>
							<a class="qeema-portfolio-teaser__cat" <?php echo ! empty( $cat['link']['url'] ) ? 'href="' . esc_url( $cat['link']['url'] ) . '"' : ''; ?>>
								<?php echo esc_html( $cat['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

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
}
