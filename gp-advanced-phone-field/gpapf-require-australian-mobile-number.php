<?php
/**
 * Gravity Perks // Advanced Phone Field // Require Australian Mobile Number
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 */
// Update "123" to your form ID and "4" to your Phone field ID.
add_filter( 'gform_field_validation_123_4', function( $result, $value ) {
	if ( $result['is_valid'] && substr( $value, 0, 4 ) !== '+614' ) {
		$result['is_valid'] = false;
		$result['message']  = 'Please enter a mobile number.';
	}
	return $result;
}, 10, 2 );
