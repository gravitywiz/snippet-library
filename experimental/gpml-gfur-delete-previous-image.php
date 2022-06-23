<?php
/**
 * Gravity Perks // Media Library + Gravity Forms User Registration // Delete Previous Image
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'update_user_metadata', function( $return, $object_id, $meta_key, $meta_value, $prev_value ) {

	// Update "your_meta_key" to the user meta key you are mapping your GPML-enabled field to.
	if ( $meta_key !== 'your_meta_key' ) {
		return $return;
	}

	$prev_value = get_user_meta( $object_id, $meta_key, true );
	$file_id    = attachment_url_to_postid( $prev_value );
	if ( $file_id ) {
		wp_delete_attachment( $file_id );
	}

	return $return;
}, 10, 5 );
