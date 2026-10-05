<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The post template renders its featured image above the fold. LiteSpeed's
 * image lazy loader replaces it with a placeholder even when WordPress gives
 * it high fetch priority, so keep only this image on the normal image path.
 */
function qeema_prioritize_post_featured_image( $attr, $attachment, $size ) {
	if ( is_admin() || ! is_singular( 'post' ) ) {
		return $attr;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id || (int) get_post_thumbnail_id( $post_id ) !== (int) $attachment->ID ) {
		return $attr;
	}

	$attr['loading']      = 'eager';
	$attr['fetchpriority'] = 'high';
	$attr['data-no-lazy'] = '1';
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'qeema_prioritize_post_featured_image', 10, 3 );
