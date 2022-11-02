<?php
/**
 * Gravity Wiz // Gravity Forms // Set Post Status by Product Field Value
 * https://gravitywiz.com/set-post-status-by-field-value-advanced/
 */
// Update "123" to the ID of your form.
add_filter( 'gform_post_data_123', 'gform_dynamic_post_status', 10, 3 );
function gform_dynamic_post_status( $post_data, $form, $entry ) {

	// update "4" to the ID of your custom post status field
	if ( $entry[4] ) {
		$values = explode( '|', $entry[6] );
		switch ( $values[0] ) {
			case 'Basic Package':
				$post_data['post_status'] = 'draft';
				break;
			case 'Premium Package':
				$post_data['post_status'] = 'publish';
				break;
		}
	}

	return $post_data;
}
