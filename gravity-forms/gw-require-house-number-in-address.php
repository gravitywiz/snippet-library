<?php
/**
 * Gravity Wiz // Gravity Forms // Require a house number in a submitted address
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 *
 * Instructions:
 *  * See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *  * Customize FORMID and FIELDID accordingly.
 */
add_filter( 'gform_field_validation_FORMID_FIELDID', function( $result, $value, $form, $field ) {
	if ( $field->type !== 'address' ) {
		return $result;
	}

	$street = trim( rgar( $value, $field->id . '.1' ) );

	// Look for a standalone number at the beginning or end of the Address Line 1 value
	if ( ! preg_match( '/^\d+\s+/', $street ) && ! preg_match( '/\s+\d+$/', $street ) ) {
		$result['is_valid'] = false;
		$result['message']  = empty( $field->errorMessage ) ? 'Please provide an address with a house number.' : $field->errorMessage;

		$field->set_input_validation_state( 1, false );
	}

	return $result;
}, 10, 4 );
