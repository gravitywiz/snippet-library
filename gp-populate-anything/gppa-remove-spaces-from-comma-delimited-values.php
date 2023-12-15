<?php
/**
 * Gravity Perks // Populate Anything // Remove Spaces from Comma-delimited Values
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_array_value_to_text', function( $text_value, $array_value, $field ) {
	// Update "123" to your form ID and "4" to the field ID being populated.
	if ( $field->formId === 123 && $field->id === 4 ) {
		$text_value = implode( ',', $array_value );
	}
	return $text_value;
}, 11, 3 );
