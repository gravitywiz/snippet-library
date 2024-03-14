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
		$valid_values = array_filter( array_column( $field->choices, 'value' ), function ( $value ) {
			return is_string( $value ) || is_int( $value );
		});
		$value_indices = array_flip( $valid_values );
		if ( is_array( $value_indices ) ) {
			usort( $value_array, function ( $x, $y ) use ( $value_indices ) {
				return $value_indices[ $x ] - $value_indices[ $y ];
			});
		}
	}

	// Encode back to JSON string.
	$value = json_encode( $value_array );

	return $value;
}, 10, 4 );
