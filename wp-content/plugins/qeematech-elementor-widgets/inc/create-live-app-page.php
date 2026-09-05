<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the "Live App" page (the 7th أعمالنا nav-dropdown link — the only
 * one that isn't a portfolio-category grid; the old site's real version is
 * an app-store showcase). No content-creation script needed for this one:
 * unlike the 6 category pages, this reuses app-store-proof-widget.php's
 * existing manual REPEATER control, pre-populated here from REAL data
 * already sitting on 10 of the 47 "تطبيقات-الهاتف" portfolio posts (their
 * `android`/`ios` ACF fields + their existing banner/thumbnail image) —
 * not fabricated placeholder entries. The old site's page shows ~56 apps;
 * only 10 of this site's migrated posts have a genuine (non-placeholder)
 * store link populated, so this page is intentionally smaller than the old
 * site's version rather than inventing data for the rest. One post
 * ("كاش باك") has both `android`/`ios` set to a literal localhost URL —
 * excluded as clearly not a real store link.
 */
function qeema_live_app_page_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_live_app_page_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => qeema_live_app_page_qid(),
		'elType'     => 'widget',
		'settings'   => $settings,
		'elements'   => array(),
		'widgetType' => $widget_type,
	);
}

/**
 * One row per app (not per store) — an app with both Android+iOS gets both
 * buttons on the same card, matching app-store-proof-widget.php's current
 * repeater shape (`logo` / `app_name` / `google_play_link` / `apple_link`).
 *
 * Sources from the `live-apps` CPT (56 real apps, imported via
 * inc/import-live-apps-endpoint.php from the old site's own `/live-app/`
 * showcase) rather than the `portfolio` CPT. Earlier in this project
 * `live-apps` was empty, so this page was built from the ~13 portfolio
 * posts that happened to carry real store links as a stand-in — now that
 * the real data exists, this is the actual source of truth and gives full
 * parity with the old site's app count instead of a partial substitute.
 * Each app's own featured image (sideloaded from the old site during that
 * import) IS its real store icon — no separate logo field/fetch needed.
 *
 * Ordered by each post's native `menu_order` (the "Order" field the
 * `page-attributes` support adds to the Live App edit screen), falling back
 * to title for any posts an admin hasn't manually ordered yet (everything
 * defaults to menu_order 0 until set), so an admin can control the display
 * order just by typing numbers — no extra plugin needed.
 */
function qeema_live_app_page_build_apps() {
	$query = new WP_Query( array(
		'post_type'      => 'live-apps',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );

	$apps = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id = get_the_ID();

		$android = get_post_meta( $post_id, 'google_play_link', true );
		$ios     = get_post_meta( $post_id, 'apple_link', true );
		if ( ! $android && ! $ios ) {
			continue;
		}

		$image_id = get_post_thumbnail_id( $post_id );
		if ( ! $image_id ) {
			continue; // no real icon available — skip rather than show an empty plate
		}
		$image_url   = wp_get_attachment_image_url( $image_id, 'full' );
		$description = get_post_meta( $post_id, 'description', true );

		$apps[] = array(
			'logo'             => array( 'url' => $image_url, 'id' => $image_id ),
			'app_name'         => get_the_title(),
			'description'      => $description ? mb_substr( $description, 0, 90 ) : '',
			'google_play_link' => array( 'url' => $android ),
			'apple_link'       => array( 'url' => $ios ),
		);
	}
	wp_reset_postdata();

	return $apps;
}

/**
 * A small "most featured" highlight reel shown above the full grid, using
 * app-store-proof-widget.php's 3-card fan (the same widget already used as
 * a proof-of-work teaser on the mobile-app-development service page). Only
 * dual-platform apps (real Android AND iOS links) qualify for this slot —
 * an objective, non-fabricated "flagship" signal rather than a manually
 * picked favorites list. The widget itself only ever renders the first 3
 * entries it's given; a couple of spares are included here as a buffer in
 * case any of the front-runners are missing a thumbnail by the time this
 * runs.
 */
function qeema_live_app_page_build_featured_apps( $limit = 4 ) {
	$query = new WP_Query( array(
		'post_type'      => 'live-apps',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );

	$featured = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id = get_the_ID();
		$android = get_post_meta( $post_id, 'google_play_link', true );
		$ios     = get_post_meta( $post_id, 'apple_link', true );
		if ( ! $android || ! $ios ) {
			continue;
		}
		$image_id = get_post_thumbnail_id( $post_id );
		if ( ! $image_id ) {
			continue;
		}
		$featured[] = array(
			'logo'             => array( 'url' => wp_get_attachment_image_url( $image_id, 'full' ), 'id' => $image_id ),
			'app_name'         => get_the_title(),
			'google_play_link' => array( 'url' => $android ),
			'apple_link'       => array( 'url' => $ios ),
		);
		if ( count( $featured ) >= $limit ) {
			break;
		}
	}
	wp_reset_postdata();

	return $featured;
}

/**
 * Curated subset of real apps for the hero's "Store Screens" visual (a
 * decorative teaser, not the full catalog — the grid below already shows
 * every app). Takes the first N thumbnailed apps with at least one real
 * store link, in the same menu_order ordering as the main grid, so
 * whichever apps an admin puts first there are also what appears in the
 * hero. Includes each app's real `description` ACF field (used as the
 * hero row's subtitle) instead of a fabricated rating/category — this
 * project's own established rule, per app-store-proof-widget.php's own
 * "no fabricated star-rating/download numbers" comment.
 */
function qeema_live_app_page_build_facet_apps( $limit = 8 ) {
	$query = new WP_Query( array(
		'post_type'      => 'live-apps',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'meta_query'     => array(
			'relation' => 'AND',
			array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ),
			array(
				'relation' => 'OR',
				array( 'key' => 'google_play_link', 'value' => '', 'compare' => '!=' ),
				array( 'key' => 'apple_link', 'value' => '', 'compare' => '!=' ),
			),
		),
	) );

	$apps = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id  = get_the_ID();
		$image_id = get_post_thumbnail_id( $post_id );
		if ( ! $image_id ) {
			continue;
		}
		$description = get_post_meta( $post_id, 'description', true );
		$apps[] = array(
			'logo'             => array( 'url' => wp_get_attachment_image_url( $image_id, 'medium' ), 'id' => $image_id ),
			'name'             => get_the_title(),
			'description'      => $description ? mb_substr( $description, 0, 42 ) : '',
			'google_play_link' => array( 'url' => get_post_meta( $post_id, 'google_play_link', true ) ),
			'apple_link'       => array( 'url' => get_post_meta( $post_id, 'apple_link', true ) ),
		);
	}
	wp_reset_postdata();

	return $apps;
}

/**
 * Rebuilds the already-live "live-app" page's Elementor content from the
 * (now populated) `live-apps` CPT, once the import completes. Uses the
 * same raw-$wpdb+transaction+before/after-verify protocol established
 * throughout this project for editing already-live pages' `_elementor_data`
 * — not a delete+recreate, so the page keeps its ID/URL. A no-op (returns
 * without writing) if the CPT is still empty, so a partial/failed import
 * never overwrites the existing working page with a hollow one.
 */
function qeema_refresh_live_app_page_from_cpt() {
	global $wpdb;

	$apps = qeema_live_app_page_build_apps();
	if ( empty( $apps ) ) {
		return false;
	}
	$featured = qeema_live_app_page_build_featured_apps();

	$page = get_page_by_path( 'live-app', OBJECT, 'page' );
	if ( ! $page ) {
		return false;
	}
	$page_id = $page->ID;

	$hero_section = array(
		'id'       => qeema_live_app_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_live_app_page_widget( 'qeema-works-hero-slider', array(
				'slides'             => array( array(
					'badge'          => '',
					'heading'        => 'تطبيقات صنعتها قيمة تك وأصبحت الآن على المتاجر',
					'description'    => 'استعرض مجموعة من التطبيقات التي قمنا بتصميمها وتطويرها وإطلاقها رسميًا، لتتحول من فكرة إلى منتج رقمي جاهز للاستخدام، يعكس خبرتنا في بناء منتجات عالية الجودة وقابلة للنمو.',
					'visual_variant' => 'facets',
					'icon'           => '',
					'facet_apps'     => qeema_live_app_page_build_facet_apps( 8 ),
					'cta_text'       => 'اطلب مشروع مشابه',
					'cta_link'       => array( 'url' => '/أتصل-بنا/' ),
				) ),
				'secondary_cta_text' => 'شاهد التطبيقات',
			) ),
		),
	);

	$featured_section = array(
		'id'       => qeema_live_app_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_live_app_page_widget( 'qeema-app-store-proof', array(
				'badge'      => 'الأكثر تميزًا',
				'heading'    => 'أبرز تطبيقاتنا على المتاجر',
				'subheading' => 'نماذج حقيقية من تطبيقات صممناها وطورناها وأطلقناها فعليًا على Android وiOS.',
				'apps'       => $featured,
				'chips'      => array(
					array( 'text' => 'حقيقي 100%' ),
					array( 'text' => 'Android وiOS' ),
					array( 'text' => 'تصميم بتفاصيل دقيقة' ),
				),
			) ),
		),
	);

	$apps_section = array(
		'id'       => qeema_live_app_page_qid(),
		'isInner'  => false,
		'elType'   => 'container',
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_live_app_page_widget( 'qeema-live-apps-grid', array(
				'badge'      => '',
				'heading'    => 'تطبيقاتنا على المتاجر',
				'subheading' => 'أكثر من ' . count( $apps ) . ' تطبيقًا حقيقيًا صممناه وطورناه وأطلقناه على Google Play وApp Store.',
				'apps'       => $apps,
			) ),
		),
	);

	$cta_section = array(
		'id'       => qeema_live_app_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_live_app_page_widget( 'qeema-cta-banner', array(
				'eyebrow'    => '',
				'heading'    => 'مستعد تبني تطبيقك القادم معنا؟',
				'subheading' => 'لنحوّل فكرتك إلى تطبيق حقيقي على المتاجر، بخبرة وشغف وتنفيذ يليق بطموحك.',
				'stat_recap' => '',
				'buttons'    => array(
					array(
						'text'  => 'ابدأ تطبيقك الآن',
						'link'  => array( 'url' => '/أتصل-بنا/' ),
						'style' => 'primary',
					),
				),
			) ),
		),
	);

	$layout = array( $hero_section, $featured_section, $apps_section, $cta_section );
	$json   = wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	// No wp_slash() here — that's only for functions like update_post_meta()
	// that internally wp_unslash() before storing. A raw $wpdb->update() call
	// stores the string exactly as given (its own escaping is query-safety
	// only), so pre-slashing would bake literal backslashes into the stored
	// JSON and corrupt it.
	$wpdb->query( 'START TRANSACTION' );
	$updated = $wpdb->update(
		$wpdb->postmeta,
		array( 'meta_value' => $json ),
		array( 'post_id' => $page_id, 'meta_key' => '_elementor_data' ),
		array( '%s' ),
		array( '%d', '%s' )
	);

	// The raw $wpdb->update() above bypasses update_post_meta(), so the object
	// cache still holds the pre-write value — clear it before re-reading,
	// otherwise get_post_meta() below returns stale data and this check
	// always fails/rolls back even when the write itself succeeded.
	clean_post_cache( $page_id );

	$after       = get_post_meta( $page_id, '_elementor_data', true );
	$decoded     = json_decode( $after, true );
	$decode_ok   = is_array( $decoded ) && 4 === count( $decoded );
	$has_apps    = false !== strpos( (string) $after, 'qeema-live-apps-grid' );
	$has_featured = false !== strpos( (string) $after, 'qeema-app-store-proof' );
	$app_count_ok = substr_count( (string) $after, '"app_name"' ) === ( count( $apps ) + count( $featured ) );

	if ( false === $updated || ! $decode_ok || ! $has_apps || ! $has_featured || ! $app_count_ok ) {
		$wpdb->query( 'ROLLBACK' );
		return false;
	}
	$wpdb->query( 'COMMIT' );

	clean_post_cache( $page_id );
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return true;
}

function qeema_maybe_create_live_app_page() {
	if ( get_option( 'qeema_live_app_page_ready' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'      => 'page',
		'name'           => 'live-app',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		update_option( 'qeema_live_app_page_ready', true, true );
		return;
	}

	$apps = qeema_live_app_page_build_apps();
	if ( empty( $apps ) ) {
		// Nothing real to show yet — don't create a hollow page. Will retry
		// on the next request once real store-link data exists.
		return;
	}
	$featured = qeema_live_app_page_build_featured_apps();

	$hero_section = array(
		'id'       => qeema_live_app_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_live_app_page_widget( 'qeema-works-hero-slider', array(
				'slides'             => array( array(
					'badge'          => '',
					'heading'        => 'تطبيقات صنعتها قيمة تك وأصبحت الآن على المتاجر',
					'description'    => 'استعرض مجموعة من التطبيقات التي قمنا بتصميمها وتطويرها وإطلاقها رسميًا، لتتحول من فكرة إلى منتج رقمي جاهز للاستخدام، يعكس خبرتنا في بناء منتجات عالية الجودة وقابلة للنمو.',
					'visual_variant' => 'facets',
					'icon'           => '',
					'facet_apps'     => qeema_live_app_page_build_facet_apps( 8 ),
					'cta_text'       => 'اطلب مشروع مشابه',
					'cta_link'       => array( 'url' => '/أتصل-بنا/' ),
				) ),
				'secondary_cta_text' => 'شاهد التطبيقات',
			) ),
		),
	);

	$featured_section = array(
		'id'       => qeema_live_app_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_live_app_page_widget( 'qeema-app-store-proof', array(
				'badge'      => 'الأكثر تميزًا',
				'heading'    => 'أبرز تطبيقاتنا على المتاجر',
				'subheading' => 'نماذج حقيقية من تطبيقات صممناها وطورناها وأطلقناها فعليًا على Android وiOS.',
				'apps'       => $featured,
				'chips'      => array(
					array( 'text' => 'حقيقي 100%' ),
					array( 'text' => 'Android وiOS' ),
					array( 'text' => 'تصميم بتفاصيل دقيقة' ),
				),
			) ),
		),
	);

	$apps_section = array(
		'id'       => qeema_live_app_page_qid(),
		'isInner'  => false,
		'elType'   => 'container',
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_live_app_page_widget( 'qeema-live-apps-grid', array(
				'badge'      => '',
				'heading'    => 'تطبيقاتنا على المتاجر',
				'subheading' => 'أكثر من ' . count( $apps ) . ' تطبيقًا حقيقيًا صممناه وطورناه وأطلقناه على Google Play وApp Store.',
				'apps'       => $apps,
			) ),
		),
	);

	$cta_section = array(
		'id'       => qeema_live_app_page_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array(
			qeema_live_app_page_widget( 'qeema-cta-banner', array(
				'eyebrow'    => '',
				'heading'    => 'مستعد تبني تطبيقك القادم معنا؟',
				'subheading' => 'لنحوّل فكرتك إلى تطبيق حقيقي على المتاجر، بخبرة وشغف وتنفيذ يليق بطموحك.',
				'stat_recap' => '',
				'buttons'    => array(
					array(
						'text'  => 'ابدأ تطبيقك الآن',
						'link'  => array( 'url' => '/أتصل-بنا/' ),
						'style' => 'primary',
					),
				),
			) ),
		),
	);

	$layout = array( $hero_section, $featured_section, $apps_section, $cta_section );

	$page_id = wp_insert_post( array(
		'post_title'     => 'Live App',
		'post_name'      => 'live-app',
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) );

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', '3.35.8' );
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $page_id, '_ez-toc-disabled', 1 );

	$json = wp_json_encode( $layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );

	$stored    = get_post_meta( $page_id, '_elementor_data', true );
	$decoded   = json_decode( $stored, true );
	$decode_ok = is_array( $decoded ) && 4 === count( $decoded );
	$has_apps  = false !== strpos( (string) $stored, 'qeema-live-apps-grid' );
	$has_featured = false !== strpos( (string) $stored, 'qeema-app-store-proof' );

	if ( ! $decode_ok || ! $has_apps || ! $has_featured ) {
		wp_delete_post( $page_id, true );
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	flush_rewrite_rules( false );

	update_post_meta( $page_id, 'rank_math_description', 'تطبيقات موبايل حقيقية صممتها وطورتها قيمة تك ونشرتها على Google Play وApp Store — استعرض أعمالنا الفعلية في تطوير تطبيقات iOS وAndroid.' );

	update_option( 'qeema_live_app_page_ready', true, true );
}
add_action( 'init', 'qeema_maybe_create_live_app_page', 20 );
