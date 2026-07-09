<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPTs and ACF fields registered here mirror production's exact post_type slugs
 * and ACF field keys (verified against the live DB), so the later content-import
 * script can copy meta values across without any remapping.
 *
 * Deliberately NOT registered: the `testimonials` / `testemonialls` / `testimonialll`
 * post types. Those are orphaned duplicates from past plugin churn with no live
 * Elementor widget referencing them — only `testimonial` (singular) is queried by
 * the site's Loop Carousel widgets.
 */

function qeema_register_post_types() {
	register_post_type( 'portfolio', array(
		'label'        => 'Portfolio',
		'labels'       => array(
			'name'          => 'Portfolio',
			'singular_name' => 'Portfolio Item',
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-portfolio',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	) );

	register_post_type( 'live-apps', array(
		'label'        => 'Live Apps',
		'labels'       => array(
			'name'          => 'Live Apps',
			'singular_name' => 'Live App',
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-smartphone',
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
	) );

	register_post_type( 'testimonial', array(
		'label'        => 'Testimonials',
		'labels'       => array(
			'name'          => 'Testimonials',
			'singular_name' => 'Testimonial',
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => false,
		'menu_icon'    => 'dashicons-testimonial',
		'supports'     => array( 'title', 'thumbnail' ),
	) );
}
add_action( 'init', 'qeema_register_post_types' );

/**
 * ACF field groups — PHP-registered (local), matching the blueprint's
 * "code, not admin-UI plugin" approach instead of CPT UI / ACF UI editing.
 */
function qeema_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Testimonial CPT fields (production field group was misleadingly named
	// "Testimonials New Syria" — renamed here, same field keys preserved).
	acf_add_local_field_group( array(
		'key'      => 'group_qeema_testimonial',
		'title'    => 'Testimonial Details',
		'fields'   => array(
			array(
				'key'   => 'field_qeema_video_link_',
				'label' => 'Video Link',
				'name'  => 'video_link_',
				'type'  => 'url',
			),
			array(
				'key'   => 'field_qeema_client_image',
				'label' => 'Client Image',
				'name'  => 'client_image',
				'type'  => 'image',
				'return_format' => 'id',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'testimonial',
				),
			),
		),
	) );

	// Live App CPT fields.
	acf_add_local_field_group( array(
		'key'      => 'group_qeema_live_apps',
		'title'    => 'Live App Details',
		'fields'   => array(
			array(
				'key'   => 'field_qeema_description',
				'label' => 'Description',
				'name'  => 'description',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_qeema_google_play_link',
				'label' => 'Google Play Link',
				'name'  => 'google_play_link',
				'type'  => 'url',
			),
			array(
				'key'   => 'field_qeema_apple_link',
				'label' => 'Apple Link',
				'name'  => 'apple_link',
				'type'  => 'url',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'live-apps',
				),
			),
		),
	) );

	// Portfolio CPT fields — field keys kept identical to production, including
	// the Arabic-named fields and the pre-existing "result_copy2-4" /
	// "required_1required_copy1-4" duplicates. Those duplicates are a known
	// cleanup opportunity (worth consolidating into repeaters later) but are
	// preserved as-is here so the import script has an exact 1:1 target.
	acf_add_local_field_group( array(
		'key'      => 'group_qeema_portfolio',
		'title'    => 'Portfolio Details',
		'fields'   => array(
			array( 'key' => 'field_qeema_idea', 'label' => 'فكرة المشروع', 'name' => 'idea', 'type' => 'wysiwyg' ),
			array( 'key' => 'field_qeema_altchdy', 'label' => 'التحدي', 'name' => 'التحدي', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alhl_mn_qymt_tk', 'label' => 'الحل من قيمة تك', 'name' => 'الحل_من_قيمة_تك', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alhl_alawl', 'label' => 'الحل الاول', 'name' => 'الحل_الاول', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alhl_althny', 'label' => 'الحل الثاني', 'name' => 'الحل_الثاني', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alhl_althlth', 'label' => 'الحل الثالث', 'name' => 'الحل_الثالث', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alhl_alrab', 'label' => 'الحل الرابع', 'name' => 'الحل_الرابع', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alhl_alkhams', 'label' => 'الحل الخامس', 'name' => 'الحل_الخامس', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alhl_alsads', 'label' => 'الحل السادس', 'name' => 'الحل_السادس', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alhl_alsab', 'label' => 'الحل السابع', 'name' => 'الحل_السابع', 'type' => 'text' ),
			array( 'key' => 'field_qeema_result', 'label' => 'النتايج', 'name' => 'result', 'type' => 'text' ),
			array( 'key' => 'field_qeema_result_copy', 'label' => 'result (copy)', 'name' => 'result_copy', 'type' => 'text' ),
			array( 'key' => 'field_qeema_result_copy2', 'label' => 'result (copy2)', 'name' => 'result_copy2', 'type' => 'text' ),
			array( 'key' => 'field_qeema_result_copy3', 'label' => 'result (copy3)', 'name' => 'result_copy3', 'type' => 'text' ),
			array( 'key' => 'field_qeema_result_copy4', 'label' => 'result (copy4)', 'name' => 'result_copy4', 'type' => 'text' ),
			array( 'key' => 'field_qeema_result_5', 'label' => 'result 5', 'name' => 'result_5', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alamyl', 'label' => 'العميل', 'name' => 'العميل', 'type' => 'text' ),
			array( 'key' => 'field_qeema_alkhdm', 'label' => 'الخدمة', 'name' => 'الخدمة', 'type' => 'text' ),
			array( 'key' => 'field_qeema_idea_copy2', 'label' => 'التنفيذ و النتيجة', 'name' => 'idea_copy2', 'type' => 'wysiwyg' ),
			array( 'key' => 'field_qeema_gallery', 'label' => 'Gallery', 'name' => 'gallery', 'type' => 'gallery' ),
			array( 'key' => 'field_qeema_link', 'label' => 'Link', 'name' => 'link', 'type' => 'url' ),
			array( 'key' => 'field_qeema_android', 'label' => 'Android', 'name' => 'android', 'type' => 'url' ),
			array( 'key' => 'field_qeema_ios', 'label' => 'iOS', 'name' => 'ios', 'type' => 'url' ),
			array( 'key' => 'field_qeema_banner', 'label' => 'بانر', 'name' => 'banner', 'type' => 'image', 'return_format' => 'id' ),
			array( 'key' => 'field_qeema_required_1required', 'label' => 'required 1', 'name' => 'required_1required', 'type' => 'text' ),
			array( 'key' => 'field_qeema_required_1required_copy', 'label' => 'required 1 (copy)', 'name' => 'required_1required_copy', 'type' => 'text' ),
			array( 'key' => 'field_qeema_required_1required_copy2', 'label' => 'required 1 (copy2)', 'name' => 'required_1required_copy2', 'type' => 'text' ),
			array( 'key' => 'field_qeema_required_1required_copy3', 'label' => 'required 1 (copy3)', 'name' => 'required_1required_copy3', 'type' => 'text' ),
			array( 'key' => 'field_qeema_required_1required_copy4', 'label' => 'required 1 (copy4)', 'name' => 'required_1required_copy4', 'type' => 'text' ),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'portfolio',
				),
			),
		),
	) );
}
add_action( 'acf/init', 'qeema_register_acf_fields' );

/**
 * ACF Options pages — same two independent options screens as production
 * (`testimonial` for the video-testimonial carousel, `ourclient` for client
 * logos), each holding one repeater with the same sub-field keys, so the two
 * carousel widgets built later can read them directly.
 */
function qeema_register_acf_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page( array(
		'page_title' => 'Video Testimonials',
		'menu_slug'  => 'testimonial',
		'menu_title' => 'Video Testimonials',
		'icon_url'   => 'dashicons-format-video',
	) );

	acf_add_options_page( array(
		'page_title' => 'Our Clients',
		'menu_slug'  => 'ourclient',
		'menu_title' => 'Our Clients',
		'icon_url'   => 'dashicons-groups',
	) );
}
add_action( 'acf/init', 'qeema_register_acf_options_pages' );

function qeema_register_acf_options_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_qeema_video_testimonials',
		'title'    => 'Video Testimonials',
		'fields'   => array(
			array(
				'key'        => 'field_qeema_testimonial_repeater',
				'label'      => 'Testimonials',
				'name'       => 'testimonial',
				'type'       => 'repeater',
				'layout'     => 'table',
				'sub_fields' => array(
					array(
						'key'   => 'field_qeema_testimonial_image',
						'label' => 'Image',
						'name'  => 'image',
						'type'  => 'image',
						'return_format' => 'id',
					),
					array(
						'key'   => 'field_qeema_testimonial_videourl',
						'label' => 'Youtube Link',
						'name'  => 'videourl',
						'type'  => 'url',
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'testimonial',
				),
			),
		),
	) );

	acf_add_local_field_group( array(
		'key'      => 'group_qeema_our_clients',
		'title'    => 'Our Clients',
		'fields'   => array(
			array(
				'key'        => 'field_qeema_client_repeater',
				'label'      => 'Clients',
				'name'       => 'client',
				'type'       => 'repeater',
				'layout'     => 'table',
				'sub_fields' => array(
					array(
						'key'   => 'field_qeema_client_logo',
						'label' => 'Logo',
						'name'  => 'logo',
						'type'  => 'image',
						'return_format' => 'id',
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'ourclient',
				),
			),
		),
	) );
}
add_action( 'acf/init', 'qeema_register_acf_options_fields' );
