<?php
/**
 * Gravity Perks // Advanced Select // Preserve Choice Order
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Preserve choice order with Multi Select fields using GP Advanced Select.
 *
 * Instruction Video: https://www.loom.com/share/d2ad3fea02234f02871ac9b1efe20b53
 *
 * Instructions:
 *
 * 1. Install this snippet by following the instructions here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gform_save_field_value', function ( $value, $lead, $field, $form ) {
	// For non GPAS values, return.
	if ( $field->type != 'multiselect' || ! gp_advanced_select()->is_advanced_select_field( $field ) ) {
		return $value;
	}

	// Decode JSON "value" string to an array.
	$value_array = json_decode( $value, true );

	// Create a map of choice values to their respective index, and sort with that.
	if ( is_array( $field->choices ) && ! empty( $field->choices ) && $value_array ) {
		// Avoid PHP notices for non-string, non-integer values with `array_flip()` by casting everything to a string and ensuring the result is an array.
		$value_indices = (array) array_flip( array_map( 'strval', array_column( $field->choices, 'value' ) ) );
		usort( $value_array, function ( $x, $y ) use ( $value_indices ) {
			return $value_indices[ $x ] - $value_indices[ $y ];
		} );
	}

	// Encode back to JSON string.
	$value = json_encode( $value_array );

	return $value;
}, 10, 4 );
