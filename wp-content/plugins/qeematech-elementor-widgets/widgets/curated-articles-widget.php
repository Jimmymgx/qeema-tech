<?php
/**
 * Curated Articles — a small, manually-picked set of real blog posts shown
 * on a service page (e.g. "مقالات ذات صلة"). Deliberately NOT an automatic
 * "latest N posts" or category query: this project's blog categories are
 * heavily fragmented/duplicated (see 2026-10-07 SEO audit), so an automatic
 * query would surface near-duplicate "أفضل شركة X في مدينة Y" posts instead
 * of the genuinely distinct guide-style articles an editor hand-picks here.
 * Reuses the `.qeema-blog-card` markup verbatim from blog-archive-widget.php
 * (same reuse-safety reasoning already established there).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qeema_Curated_Articles_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-curated-articles';
	}

	public function get_title() {
		return __( 'Curated Articles', 'qeematech-elementor-widgets' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return array( 'qeema-shared-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Curated Articles', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'مقالات ذات صلة',
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'post_id', array(
			'label'   => __( 'Post ID', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 0,
		) );

		$this->add_control( 'articles', array(
			'label'       => __( 'Articles', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(),
			'title_field' => 'Post #{{{ post_id }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$rows     = ! empty( $settings['articles'] ) ? $settings['articles'] : array();

		$ids = array();
		foreach ( $rows as $row ) {
			$id = ! empty( $row['post_id'] ) ? intval( $row['post_id'] ) : 0;
			if ( $id && get_post_status( $id ) === 'publish' ) {
				$ids[] = $id;
			}
		}
		if ( empty( $ids ) ) {
			return;
		}
		?>
		<div class="qeema-curated-articles">
			<?php if ( ! empty( $settings['heading'] ) ) : ?>
				<h3 class="qeema-curated-articles__heading"><?php echo esc_html( $settings['heading'] ); ?></h3>
			<?php endif; ?>
			<div class="qeema-blog-archive__grid qeema-curated-articles__grid">
				<?php foreach ( $ids as $post_id ) : setup_postdata( get_post( $post_id ) ); ?>
					<a class="qeema-blog-card" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
						<div class="qeema-blog-card__media">
							<?php echo get_the_post_thumbnail( $post_id, 'large' ); ?>
						</div>
						<div class="qeema-blog-card__body">
							<h3 class="qeema-blog-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
							<p class="qeema-blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 18 ) ); ?></p>
							<span class="qeema-blog-archive__readmore"><?php esc_html_e( 'عرض المزيد »', 'qeematech-elementor-widgets' ); ?></span>
						</div>
					</a>
				<?php endforeach; wp_reset_postdata(); ?>
			</div>
		</div>
		<?php
	}
}
