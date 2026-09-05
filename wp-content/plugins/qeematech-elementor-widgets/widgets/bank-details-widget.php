<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bank transfer details — a grid of accounts, each with its own logo and
 * labeled rows (bank name, account number, IBAN, etc), for wiring-instruction
 * style pages. Values render dir="ltr" regardless of the field's own script,
 * since these are Latin/numeric strings (IBAN, SWIFT) that must never
 * visually reverse inside an RTL page — getting this wrong would be a real
 * correctness bug, not just a style nitpick, on a page whose only job is
 * getting these exact characters into a client's banking app.
 */
class Qeema_Bank_Details_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-bank-details';
	}

	public function get_title() {
		return __( 'Bank Details Card', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-price-list';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	public function get_script_depends() {
		return array( 'qeema-bank-details', 'qeema-scroll-reveal' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'header_section', array(
			'label' => __( 'Header', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'badge', array(
			'label'   => __( 'Badge', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'بيانات الحساب البنكي',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'accounts_section', array(
			'label' => __( 'Accounts', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'accounts', array(
			'label'       => __( 'Accounts', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'account_logo',
					'label'   => __( 'Logo', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::MEDIA,
					'default' => array(
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					),
				),
				array(
					'name'    => 'account_title',
					'label'   => __( 'Title', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'Account',
				),
				array(
					'name'    => 'account_tag',
					'label'   => __( 'Tag (e.g. currency)', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				),
				array(
					'name'    => 'account_note',
					'label'   => __( 'Note (optional)', 'qeematech-elementor-widgets' ),
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
				),
				array(
					'name'        => 'account_rows',
					'label'       => __( 'Rows', 'qeematech-elementor-widgets' ),
					'type'        => \Elementor\Controls_Manager::REPEATER,
					'fields'      => array(
						array(
							'name'    => 'label',
							'label'   => __( 'Label', 'qeematech-elementor-widgets' ),
							'type'    => \Elementor\Controls_Manager::TEXT,
							'default' => 'Label',
						),
						array(
							'name'    => 'value',
							'label'   => __( 'Value', 'qeematech-elementor-widgets' ),
							'type'    => \Elementor\Controls_Manager::TEXT,
							'default' => '',
						),
						array(
							'name'         => 'copyable',
							'label'        => __( 'Show copy button', 'qeematech-elementor-widgets' ),
							'type'         => \Elementor\Controls_Manager::SWITCHER,
							'default'      => '',
							'label_on'     => __( 'Yes', 'qeematech-elementor-widgets' ),
							'label_off'    => __( 'No', 'qeematech-elementor-widgets' ),
							'return_value' => 'yes',
						),
					),
					'default'     => array(),
					'title_field' => '{{{ label }}}',
				),
			),
			'default'     => array(),
			'title_field' => '{{{ account_title }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="qeema-bank-details">
			<div class="qeema-bank-details__wrap">
				<div class="qeema-bank-details__head">
					<?php if ( ! empty( $settings['badge'] ) ) : ?>
						<span class="qeema-bank-details__badge"><?php echo esc_html( $settings['badge'] ); ?></span>
					<?php endif; ?>
					<h1><?php echo esc_html( $settings['heading'] ); ?></h1>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo wp_kses_post( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="qeema-bank-details__grid">
					<?php foreach ( $settings['accounts'] as $account ) : ?>
						<div class="qeema-bank-details__account qeema-reveal">
							<div class="qeema-bank-details__account-head">
								<?php if ( ! empty( $account['account_logo']['url'] ) && \Elementor\Utils::get_placeholder_image_src() !== $account['account_logo']['url'] ) : ?>
									<span class="qeema-bank-details__account-logo">
										<img src="<?php echo esc_url( $account['account_logo']['url'] ); ?>" alt="">
									</span>
								<?php endif; ?>
								<span class="qeema-bank-details__account-title"><?php echo esc_html( $account['account_title'] ); ?></span>
								<?php if ( ! empty( $account['account_tag'] ) ) : ?>
									<span class="qeema-bank-details__tag"><?php echo esc_html( $account['account_tag'] ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( ! empty( $account['account_note'] ) ) : ?>
								<p class="qeema-bank-details__note"><?php echo wp_kses_post( $account['account_note'] ); ?></p>
							<?php endif; ?>
							<?php foreach ( $account['account_rows'] as $field ) :
								$is_copyable = 'yes' === $field['copyable'];
								?>
								<div class="qeema-bank-details__row">
									<span class="qeema-bank-details__label"><?php echo esc_html( $field['label'] ); ?></span>
									<span class="qeema-bank-details__value-wrap">
										<span class="qeema-bank-details__value" dir="ltr"><?php echo esc_html( $field['value'] ); ?></span>
										<?php if ( $is_copyable ) : ?>
											<button type="button" class="qeema-bank-details__copy" data-copy-value="<?php echo esc_attr( $field['value'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Copy %s', 'qeematech-elementor-widgets' ), $field['label'] ) ); ?>">
												<i class="fas fa-copy" aria-hidden="true"></i>
											</button>
										<?php endif; ?>
									</span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
