<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static social-share pill buttons for the single-post template. No API
 * keys, no JS — just share-URL templates built from the current post's
 * permalink/title, same Font Awesome 5 icon set already loaded for the
 * site footer's social icons.
 */
class Qeema_Share_Buttons_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-share-buttons';
	}

	public function get_title() {
		return __( 'Share Buttons', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-share-arrow';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Share Buttons', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'label', array(
			'label'   => __( 'Label', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'شارك المقال',
		) );

		foreach ( array(
			'show_whatsapp' => 'WhatsApp',
			'show_twitter'  => 'X / Twitter',
			'show_linkedin' => 'LinkedIn',
			'show_telegram' => 'Telegram',
			'show_email'    => 'Email',
		) as $key => $label ) {
			$this->add_control( $key, array(
				'label'        => $label,
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'qeematech-elementor-widgets' ),
				'label_off'    => __( 'Hide', 'qeematech-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			) );
		}

		$this->end_controls_section();
	}

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$permalink = get_permalink();
		$title     = get_the_title();

		if ( ! $permalink ) {
			return;
		}

		$u = rawurlencode( $permalink );
		$t = rawurlencode( $title );

		$networks = array(
			'show_whatsapp' => array(
				'label' => __( 'واتساب', 'qeematech-elementor-widgets' ),
				'icon'  => 'fab fa-whatsapp',
				'href'  => "https://wa.me/?text={$t}%20{$u}",
			),
			'show_twitter'  => array(
				'label' => __( 'X', 'qeematech-elementor-widgets' ),
				'icon'  => 'fab fa-twitter',
				'href'  => "https://twitter.com/intent/tweet?url={$u}&text={$t}",
			),
			'show_linkedin' => array(
				'label' => __( 'لينكدإن', 'qeematech-elementor-widgets' ),
				'icon'  => 'fab fa-linkedin-in',
				'href'  => "https://www.linkedin.com/sharing/share-offsite/?url={$u}",
			),
			'show_telegram' => array(
				'label' => __( 'تيليجرام', 'qeematech-elementor-widgets' ),
				'icon'  => 'fab fa-telegram-plane',
				'href'  => "https://t.me/share/url?url={$u}&text={$t}",
			),
			'show_email'    => array(
				'label' => __( 'بريد إلكتروني', 'qeematech-elementor-widgets' ),
				'icon'  => 'fa fa-envelope',
				'href'  => "mailto:?subject={$t}&body={$u}",
			),
		);
		?>
		<div class="qeema-share-buttons">
			<?php if ( ! empty( $settings['label'] ) ) : ?>
				<span class="qeema-share-buttons__label"><?php echo esc_html( $settings['label'] ); ?></span>
			<?php endif; ?>
			<div class="qeema-share-buttons__list">
				<?php foreach ( $networks as $key => $network ) :
					if ( 'yes' !== ( $settings[ $key ] ?? '' ) ) {
						continue;
					}
					$is_email = 'show_email' === $key;
					?>
					<a class="qeema-share-buttons__btn"
						href="<?php echo esc_url( $network['href'] ); ?>"
						aria-label="<?php echo esc_attr( $network['label'] ); ?>"
						<?php echo $is_email ? '' : 'target="_blank" rel="noopener"'; ?>>
						<i class="<?php echo esc_attr( $network['icon'] ); ?>" aria-hidden="true"></i>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
