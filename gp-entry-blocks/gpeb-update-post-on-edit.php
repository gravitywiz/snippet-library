<?php
/**
 * Gravity Perks // Entry Blocks // Update Post on Entry Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Use this snippet to update the original post created by an entry when that entry is edited. By default, Gravity Forms
 * would create a new post.
 *
 * This only works with Post fields (and not the Advanced Post Creation add-on).
 */
add_filter( 'gform_post_data', function( $post_data, $form, $entry ) {
	$post_id = $entry['post_id'];
	if ( $post_id ) {
		$post_data['ID'] = $post_id;
		$post = get_post( $post_id );
		$post_data['post_status'] = $post['post_status'];
	}
	return $post_data;
}, 10, 3 );
