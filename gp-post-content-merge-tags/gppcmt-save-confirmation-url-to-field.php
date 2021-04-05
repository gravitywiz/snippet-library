<?php
/**
 * Gravity Perks // GP Post Content Merge Tags // Save Confirmation URL to Field
 * http://gravitywiz.com/documentation/gravity-forms-post-content-merge-tags/
 *
 * This snippet saves the Confirmation URL to a field. 
 */
// 1. Update "123" to your form ID.
add_filter( 'gform_entry_post_save_123', function( $entry ) {

	// 2. Update this to your desired URL.
	$url = 'http://yoururl.com/';

	// 3. Update "4" to your field ID that will be populated with the URL.
	$field_id = 4;

	/* DON'T EDIT BELOW THIS LINE */

	if( function_exists( 'gp_post_content_merge_tags' ) ) {

		$eid = gp_post_content_merge_tags()->prepare_eid( $entry['id'], true );
		$url = add_query_arg( 'eid', $eid, $url );

		GFAPI::update_entry_field( $entry['id'], $field_id, $url );
		$entry[ $field_id ] = $url;

	}

	return $entry;
} );
