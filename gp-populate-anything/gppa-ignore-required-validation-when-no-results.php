<?php
/**
 * Gravity Perks // Populate Anything // Ignore Required Validation for Fields w/ No Results
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to your Populate-Anything-populated field ID.
add_filter( 'gform_field_validation_123_4', function( $result, $value, $form, $field ) {
	if ( ! $result['is_valid'] && $result['message'] === __( 'This field is required.', 'gravityforms' ) && $field->choices[0]['gppaErrorChoice'] === 'no_choices' ) {
		$result['is_valid'] = true;
		$result['message'] = '';
	}
	return $result;
}, 10, 4 );
