<?php
/**
 * Gravity Wiz // Gravity Forms // Validate Current Password
 * http://gravitywiz.com/
 */
// Update "123" to your form ID and "4" to your Password field ID.
add_filter( 'gform_field_validation_123_4', function( $result, $value ) {
	$user = wp_get_current_user();
	if ( ! $user || ! wp_check_password( $value, $user->data->user_pass, $user->ID ) ) { 
			$result['is_valid'] = false;
			$result['message'] = 'Invalid current password. Please try again.';
	}
	return $result;
}, 10, 2 );
