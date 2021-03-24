<?php
/**
 * Gravity Wiz // Gravity Forms // Validate Current Password
 * http://gravitywiz.com/
 */
// update "123" to your form ID, update "6" to your Password field ID
add_filter( 'gform_field_validation_1639_1', function( $result, $value ) {
	$user = wp_get_current_user();
	if ( ! wp_check_password( $value, $user->data->user_pass, $user->ID ) ) {
		$result['is_valid'] = false;
		$result['message'] = 'Invalid current password. Please try again.';
	}
	return $result;
}, 10, 2 );
