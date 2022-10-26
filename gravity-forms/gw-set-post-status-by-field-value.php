<?php
/**
 * Gravity Wiz // Gravity Forms // Set Post Status by Field Value
 * https://gravitywiz.com/set-post-status-by-field-value/
 */
// update "123" to the ID of your form
add_filter( 'gform_post_data_123', 'gform_dynamic_post_status', 10, 3 );
function gform_dynamic_post_status( $post_data, $form, $entry ) {

	// update "4" to the ID of your custom post status field
	if ( $entry[4] ) {
		$post_data['post_status'] = $entry[4];
	}

	return $post_data;
}
