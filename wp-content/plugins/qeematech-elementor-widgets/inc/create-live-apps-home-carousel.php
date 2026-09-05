<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a "Live Apps" phone carousel section to the Homepage, right after the
 * Portfolio Teaser section. There's no DB relationship between the
 * `live-apps` CPT (real shipped apps) and the `portfolio` CPT (case-study
 * pages) — matched here by normalized title (stripping a leading
 * تطبيق/متجر/موقع/منصة word) since that's the only correspondence that
 * exists. An app with no confident portfolio match falls back to its real
 * Google Play/App Store link (opened in a new tab) instead of a dead card.
 */
function qeema_live_apps_home_carousel_qid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

function qeema_live_apps_home_carousel_normalize_title( $title ) {
	$title = trim( (string) $title );
	$title = preg_replace( '/^(تطبيق|متجر|موقع|منصة)\s+/u', '', $title );
	$title = preg_replace( '/\s+(app|application|store)$/i', '', $title );
	$title = trim( $title );
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $title, 'UTF-8' ) : strtolower( $title );
}

function qeema_live_apps_home_carousel_portfolio_posts() {
	$ids = get_posts( array(
		'post_type'      => 'portfolio',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );

	$out = array();
	foreach ( $ids as $id ) {
		$out[] = array(
			'id'   => $id,
			'norm' => qeema_live_apps_home_carousel_normalize_title( get_the_title( $id ) ),
		);
	}
	return $out;
}

/**
 * Exact normalized-title match first; a substring match (either direction)
 * only as a fallback, and only for names long enough (4+ chars) that a
 * coincidental substring hit is unlikely.
 */
function qeema_live_apps_home_carousel_find_project_url( $app_title, $portfolio_posts ) {
	$needle = qeema_live_apps_home_carousel_normalize_title( $app_title );
	if ( '' === $needle ) {
		return null;
	}

	foreach ( $portfolio_posts as $p ) {
		if ( $p['norm'] === $needle ) {
			return get_permalink( $p['id'] );
		}
	}

	if ( mb_strlen( $needle ) >= 4 ) {
		foreach ( $portfolio_posts as $p ) {
			if ( '' === $p['norm'] ) {
				continue;
			}
			if ( false !== mb_strpos( $p['norm'], $needle ) || false !== mb_strpos( $needle, $p['norm'] ) ) {
				return get_permalink( $p['id'] );
			}
		}
	}

	return null;
}

/**
 * Builds the `apps` repeater setting from real `live-apps` CPT data. Each
 * item links to its matching `portfolio` single (case-study) page when one
 * can be matched by title; otherwise falls back to the app's real Google
 * Play/App Store link. No fabricated ratings/categories — same rule as
 * app-store-proof-widget.php and the /live-app/ page's hero.
 */
/**
 * Curated by real Google Play / App Store performance (install counts,
 * ratings, listing still live) rather than category diversity — re-picked
 * because several previous entries (HAAT Delivery, fam Properties, Taxi
 * Coop) turned out to be other companies' own official store listings, not
 * qeema builds (confirmed via each listing's actual "developer" field
 * against qeema's real Play Store developer account), and JOYiN's Play
 * listing had gone dead (404). Misr Elkheir + Quick Discount are pinned
 * first by explicit request; the rest are the best real performers left
 * after excluding misattributed/broken listings. The full, unfiltered
 * catalog still lives on the /live-app/ page — this curation is
 * homepage-only.
 */
function qeema_live_apps_home_carousel_curated_ids() {
	return array(
		4346, // Misr Elkheir Staff — pinned
		4102, // Quick Discount — pinned, 50K+ installs, 4.64★
		4175, // Erwaa — 100K+ installs, 4.79★
		4161, // جمعية دوت كوم — 100K+ installs, 4.44★
		4203, // كبش نجد — 100K+ installs, 3.29★
		4155, // Maharatufl — 100K+ installs, 4.38★
		4159, // Ben Soliman — 100K+ installs, 3.49★
		4127, // الو لحمة — 50K+ installs
		4171, // Innvii Rent — 10K+ installs, 4.64★
		4193, // BRGR — 10K+ installs, 4.40★
		4125, // جزارتي — 10K+ installs, 3.36★, confirmed under qeema's own Play account
		4173, // Aldobi — 10K+ installs
	);
}

function qeema_live_apps_home_carousel_build_apps( $limit = 10, $curated_ids = array() ) {
	$portfolio_posts = qeema_live_apps_home_carousel_portfolio_posts();

	$query_args = array(
		'post_type'      => 'live-apps',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_query'     => array(
			'relation' => 'AND',
			array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ),
			array(
				'relation' => 'OR',
				array( 'key' => 'google_play_link', 'value' => '', 'compare' => '!=' ),
				array( 'key' => 'apple_link', 'value' => '', 'compare' => '!=' ),
			),
		),
	);

	if ( ! empty( $curated_ids ) ) {
		$query_args['post__in'] = $curated_ids;
		$query_args['orderby']  = 'post__in';
	} else {
		$query_args['orderby'] = array( 'menu_order' => 'ASC', 'title' => 'ASC' );
	}

	$query = new WP_Query( $query_args );

	$apps = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id  = get_the_ID();
		$image_id = get_post_thumbnail_id( $post_id );
		if ( ! $image_id ) {
			continue;
		}

		$name        = get_the_title();
		$description = get_post_meta( $post_id, 'description', true );
		$play        = get_post_meta( $post_id, 'google_play_link', true );
		$apple       = get_post_meta( $post_id, 'apple_link', true );
		$store_type  = $play ? 'play' : 'apple';

		$project_url = qeema_live_apps_home_carousel_find_project_url( $name, $portfolio_posts );
		$is_external = false;
		if ( ! $project_url ) {
			$project_url = $play ? $play : $apple;
			$is_external = true;
		}

		$apps[] = array(
			'logo'          => array( 'url' => wp_get_attachment_image_url( $image_id, 'medium' ), 'id' => $image_id ),
			'name'          => $name,
			'developer'     => 'قيمة تك',
			'description'   => $description ? mb_substr( $description, 0, 90 ) : '',
			'store_type'    => $store_type,
			'link'          => array( 'url' => (string) $project_url ),
			'link_external' => $is_external ? 'yes' : '',
		);
	}
	wp_reset_postdata();

	return $apps;
}

/**
 * Inserts the Live Apps carousel container right after the Portfolio Teaser
 * section on the Homepage. Idempotent (bails if the widget type is already
 * present) and uses the same raw-$wpdb write protocol as the rest of this
 * project: no wp_slash() before the update, clean_post_cache() before
 * re-reading to verify, transaction-wrapped.
 */
function qeema_maybe_add_live_apps_carousel_to_home() {
	if ( get_option( 'qeema_home_live_apps_carousel_ready' ) ) {
		return;
	}

	global $wpdb;

	$page_id = (int) get_option( 'page_on_front' );
	if ( ! $page_id ) {
		return;
	}

	$raw = get_post_meta( $page_id, '_elementor_data', true );
	if ( false !== strpos( (string) $raw, 'qeema-live-apps-carousel' ) ) {
		update_option( 'qeema_home_live_apps_carousel_ready', true, true );
		return;
	}

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return;
	}

	$apps = qeema_live_apps_home_carousel_build_apps( 12, qeema_live_apps_home_carousel_curated_ids() );
	if ( empty( $apps ) ) {
		return; // nothing real to show yet — don't insert a hollow section
	}

	$section = array(
		'id'       => qeema_live_apps_home_carousel_qid(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array( 'content_width' => 'full' ),
		'elements' => array( array(
			'id'         => qeema_live_apps_home_carousel_qid(),
			'elType'     => 'widget',
			'widgetType' => 'qeema-live-apps-carousel',
			'settings'   => array(
				'badge'      => 'تطبيقاتنا الحية',
				'heading'    => 'تطبيقات حقيقية شغّالة على المتاجر دلوقتي',
				'subheading' => 'جزء من التطبيقات اللي صممناها وطورناها لعملائنا — اضغط على أي تطبيق قدّامك عشان تشوف قصته كاملة.',
				'apps'       => $apps,
			),
			'elements'   => array(),
			'isInner'    => false,
		) ),
	);

	$insert_after_index = null;
	foreach ( $data as $idx => $node ) {
		$widget_type = $node['elements'][0]['widgetType'] ?? '';
		if ( 'qeema-portfolio-teaser' === $widget_type ) {
			$insert_after_index = $idx;
			break;
		}
	}
	if ( null === $insert_after_index ) {
		$insert_after_index = count( $data ) - 1; // fall back to appending at the end
	}

	array_splice( $data, $insert_after_index + 1, 0, array( $section ) );

	$json = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	$wpdb->query( 'START TRANSACTION' );
	$updated = $wpdb->update(
		$wpdb->postmeta,
		array( 'meta_value' => $json ),
		array( 'post_id' => $page_id, 'meta_key' => '_elementor_data' ),
		array( '%s' ),
		array( '%d', '%s' )
	);

	clean_post_cache( $page_id );

	$after     = get_post_meta( $page_id, '_elementor_data', true );
	$decoded   = json_decode( $after, true );
	$decode_ok = is_array( $decoded ) && count( $decoded ) === count( $data );
	$has_it    = false !== strpos( (string) $after, 'qeema-live-apps-carousel' );

	if ( false === $updated || ! $decode_ok || ! $has_it ) {
		$wpdb->query( 'ROLLBACK' );
		return;
	}
	$wpdb->query( 'COMMIT' );

	clean_post_cache( $page_id );
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'qeema_home_live_apps_carousel_ready', true, true );
}
add_action( 'init', 'qeema_maybe_add_live_apps_carousel_to_home', 20 );

/**
 * Re-pulls the `apps` setting on the already-inserted Live Apps carousel
 * widget from the current `live-apps` CPT — unlike the insert-once function
 * above (which is guarded by an idempotency flag and never touches the page
 * again), this is meant to be called by hand whenever the CPT changes (e.g.
 * after importing more real apps) so the homepage reflects the latest data.
 * Same raw-$wpdb write protocol as the rest of this project.
 */
function qeema_refresh_live_apps_home_carousel() {
	global $wpdb;

	$page_id = (int) get_option( 'page_on_front' );
	if ( ! $page_id ) {
		return false;
	}

	$raw  = get_post_meta( $page_id, '_elementor_data', true );
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return false;
	}

	$apps = qeema_live_apps_home_carousel_build_apps( 12, qeema_live_apps_home_carousel_curated_ids() );
	if ( empty( $apps ) ) {
		return false;
	}

	$found = false;
	foreach ( $data as &$node ) {
		if ( ( $node['elements'][0]['widgetType'] ?? '' ) === 'qeema-live-apps-carousel' ) {
			$node['elements'][0]['settings']['apps'] = $apps;
			$found                                   = true;
			break;
		}
	}
	unset( $node );

	if ( ! $found ) {
		return false; // widget isn't on the page yet — nothing to refresh
	}

	$json = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	$wpdb->query( 'START TRANSACTION' );
	$updated = $wpdb->update(
		$wpdb->postmeta,
		array( 'meta_value' => $json ),
		array( 'post_id' => $page_id, 'meta_key' => '_elementor_data' ),
		array( '%s' ),
		array( '%d', '%s' )
	);

	clean_post_cache( $page_id );

	$after     = get_post_meta( $page_id, '_elementor_data', true );
	$decoded   = json_decode( $after, true );
	$decode_ok = is_array( $decoded ) && count( $decoded ) === count( $data );

	if ( false === $updated || ! $decode_ok ) {
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
