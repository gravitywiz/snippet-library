<?php
/**
 * Gravity Perks // GP Populate Anything // Use Newline As The Delimiter for Paragraph Text fields
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_array_value_to_text', function( $text_value, $array_value, $field ) {
	if ( $field->type !== 'textarea' ) {
		return $text_value;
	}

	return implode( "\n", $array_value );
}, 11, 3 );
