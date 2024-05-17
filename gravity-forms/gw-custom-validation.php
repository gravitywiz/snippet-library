<?php
/**
 * Gravity Wiz // Gravity Forms // Custom Validation
 *
 * This snippet is an example of how to add custom validation to a Gravity Form field that is activated by adding
 * a class to the "Custom CSS Class" field setting under the "Appearance" panel of the field settings.
 *
 * Instructions:
 *   1. Add to snippet to site. See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/.
 *   2. Add `validation-no-period` to the "Custom CSS Class" field setting of the field(s) you want to validate.
 */
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
	// Only validate fields that contain the class 'no-period'
	if ( strpos( $field->cssClass, 'validation-no-period' ) === false ) {
		return $result;
	}

	if ( strpos( $value, '.' ) !== false ) {
		$result['is_valid'] = false;
		$result['message'] = 'Please enter a valid value.';
	}

	return $result;
}, 10, 4 );
