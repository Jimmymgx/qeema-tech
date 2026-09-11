<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Homepage "Selected Work" section — a small, curated teaser pulling only the
 * `portfolio` posts an admin has explicitly flagged via the `featured_homepage`
 * ACF true/false field (field_qeema_featured_homepage), capped to a handful
 * of items instead of the full-archive behavior of portfolio-teaser-widget.php
 * / portfolio-archive-widget.php.
 *
 * This field is brand new and no posts have it set yet, so an empty query is
 * the expected normal state right after this ships, not an error condition —
 * render() returns early with no markup at all in that case (and also if
 * every flagged post lacks a usable image) rather than rendering an
 * empty/placeholder section, matching the defensive pattern used throughout
 * this plugin (e.g. portfolio-archive-widget.php's empty-state message,
 * feature-grid's empty repeater fields) of never fabricating content that
 * isn't actually configured.
 *
 * Image resolution reuses the exact thumbnail-or-ACF-`banner` fallback (and
 * skip-the-post-if-neither-exists rule) from portfolio-archive-widget.php's
 * render_archive_content() so a post migrated with only a `banner` field
 * still shows up here instead of silently vanishing.
 */
class Qeema_Selected_Work_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'qeema-selected-work';
	}

	public function get_title() {
		return __( 'Selected Work (Homepage)', 'qeematech-elementor-widgets' );
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
			'default' => 'أعمال مختارة',
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Subheading', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'نماذج من أحدث المشروعات التي عملنا عليها',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'grid_section', array(
			'label' => __( 'Grid Settings', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'posts_per_page', array(
			'label'   => __( 'Number of Projects', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 6,
			'min'     => 1,
			'max'     => 8,
			'step'    => 1,
		) );

		$this->add_control( 'group_by_category', array(
			'label'        => __( 'Group By Category', 'qeematech-elementor-widgets' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => '',
			'description'  => __( 'When on, the selected projects are grouped under their portfolio-categories term instead of one flat grid.', 'qeematech-elementor-widgets' ),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'link_section', array(
			'label' => __( 'Archive Link', 'qeematech-elementor-widgets' ),
		) );

		$this->add_control( 'archive_link', array(
			'label'       => __( 'Full Portfolio Page Link', 'qeematech-elementor-widgets' ),
			'type'        => \Elementor\Controls_Manager::URL,
			'default'     => array(
				'url' => '',
			),
		) );

		$this->add_control( 'archive_link_text', array(
			'label'   => __( 'Link Text', 'qeematech-elementor-widgets' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'عرض كل الأعمال',
		) );

		$this->end_controls_section();
	}

	/**
	 * Builds the renderable item list from the current WP_Query loop —
	 * separated from render() so the "did anything actually survive the
	 * image-fallback filter" check can happen before any markup is echoed.
	 */
	private function collect_items( \WP_Query $query ) {
		$items = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();

			$image_id = get_post_thumbnail_id( $post_id );
			if ( ! $image_id ) {
				// Same fallback as portfolio-archive-widget.php: migrated
				// content sometimes only has an image in the ACF `banner`
				// field rather than a real featured image.
				$image_id = (int) get_post_meta( $post_id, 'banner', true );
			}
			if ( ! $image_id ) {
				continue;
			}

			$terms = get_the_terms( $post_id, 'portfolio-categories' );
			$cats  = array();
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$cats[] = $term->name;
				}
			}

			$items[] = array(
				'image_id'  => $image_id,
				'title'     => get_the_title( $post_id ),
				'permalink' => get_permalink( $post_id ),
				'cats'      => $cats,
			);
		}
		wp_reset_postdata();

		return $items;
	}

	/**
	 * Buckets the already-capped item list by each item's first
	 * portfolio-categories term, preserving first-appearance order so the
	 * group order follows the (date DESC) query order rather than an
	 * unrelated taxonomy order. Items with no term at all (shouldn't happen
	 * in practice, but defensively handled) fall into a plain "أخرى" bucket
	 * instead of being dropped.
	 */
	private function group_items_by_category( $items ) {
		$groups    = array();
		$other_key = '__other__';

		foreach ( $items as $item ) {
			$key = ! empty( $item['cats'] ) ? $item['cats'][0] : $other_key;
			if ( ! isset( $groups[ $key ] ) ) {
				$groups[ $key ] = array(
					'label' => $other_key === $key ? __( 'أخرى', 'qeematech-elementor-widgets' ) : $key,
					'items' => array(),
				);
			}
			$groups[ $key ]['items'][] = $item;
		}

		return $groups;
	}

	private function render_card( $item ) {
		$image_url = wp_get_attachment_image_url( $item['image_id'], 'large' );
		?>
		<a class="qeema-selected-work__card" href="<?php echo esc_url( $item['permalink'] ); ?>">
			<span class="qeema-selected-work__media" style="background-image:url('<?php echo esc_url( $image_url ); ?>')"></span>
			<span class="qeema-selected-work__body">
				<?php if ( ! empty( $item['cats'] ) ) : ?>
					<span class="qeema-selected-work__cats">
						<?php foreach ( $item['cats'] as $cat_name ) : ?>
							<span class="qeema-selected-work__cat"><?php echo esc_html( $cat_name ); ?></span>
						<?php endforeach; ?>
					</span>
				<?php endif; ?>
				<span class="qeema-selected-work__title"><?php echo esc_html( $item['title'] ); ?></span>
			</span>
		</a>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$posts_per_page = ! empty( $settings['posts_per_page'] ) ? intval( $settings['posts_per_page'] ) : 6;
		$posts_per_page = max( 1, min( 8, $posts_per_page ) );

		$query = new \WP_Query( array(
			'post_type'           => 'portfolio',
			'post_status'         => 'publish',
			'posts_per_page'      => $posts_per_page,
			// ACF true/false fields store '1'/'0' as strings in postmeta -
			// this is the brand-new field_qeema_featured_homepage field, not
			// set on any of the 167 existing portfolio posts yet, so an
			// empty result here is the expected normal state, not a bug.
			'meta_key'            => 'featured_homepage', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'          => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		) );

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		$items = $this->collect_items( $query );

		// Every flagged post lacked a usable image (or, more likely right
		// now, none are flagged at all) - render nothing rather than an
		// empty/broken-looking section.
		if ( ! $items ) {
			return;
		}

		$group_by_category = 'yes' === ( $settings['group_by_category'] ?? '' );
		?>
		<section class="qeema-selected-work">
			<div class="qeema-selected-work__wrap">
				<div class="qeema-selected-work__head">
					<?php if ( ! empty( $settings['heading'] ) ) : ?>
						<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $settings['subheading'] ) ) : ?>
						<p><?php echo esc_html( $settings['subheading'] ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( $group_by_category ) : ?>
					<?php foreach ( $this->group_items_by_category( $items ) as $group ) : ?>
						<div class="qeema-selected-work__group">
							<h3 class="qeema-selected-work__group-title"><?php echo esc_html( $group['label'] ); ?></h3>
							<div class="qeema-selected-work__grid">
								<?php foreach ( $group['items'] as $item ) : ?>
									<?php $this->render_card( $item ); ?>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="qeema-selected-work__grid">
						<?php foreach ( $items as $item ) : ?>
							<?php $this->render_card( $item ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $settings['archive_link']['url'] ) ) : ?>
					<div class="qeema-selected-work__actions">
						<a class="qeema-selected-work__btn"
							href="<?php echo esc_url( $settings['archive_link']['url'] ); ?>"
							<?php echo ! empty( $settings['archive_link']['is_external'] ) ? ' target="_blank"' : ''; ?>
							<?php echo ! empty( $settings['archive_link']['nofollow'] ) ? ' rel="nofollow"' : ''; ?>>
							<?php echo esc_html( ! empty( $settings['archive_link_text'] ) ? $settings['archive_link_text'] : __( 'عرض كل الأعمال', 'qeematech-elementor-widgets' ) ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
