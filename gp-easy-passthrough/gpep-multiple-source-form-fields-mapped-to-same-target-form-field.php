<?php
/**
 * Gravity Perks // Easy Passthrough // Multiple Source Form fields mapped to same Target Form Field.
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * Instruction Video: https://www.loom.com/share/d3ae6ba5370a44da9d6c16d36b048c5b
 *
 * If multiple Source Form Fields Mapped to the same Target Form Field, use a previous mapping rule in case the current one does not have any value.
 */
// Replace 2 with the target form ID and 1 with the target form field
add_filter( 'gpep_target_field_value_2_1', function( $field_value, $form_id, $target_field_id, $source_field ) {
	static $possible_gpep_field_values = array();

	// if current value is not empty, add to the static array
	if ( ! empty( $field_value) ) {
		array_push( $possible_gpep_field_values, $field_value );
	} else {
		// if current value is empty, try to restore the last added value
		if ( ! empty( $possible_gpep_field_values ) ) {
			$field_value = end( $possible_gpep_field_values );
		}
	}
	return $field_value;
}, 10, 4 );
