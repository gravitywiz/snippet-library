<?php
/**
 * Gravity Perks // Populate Anything // Preserve Field Selections on Repopulation
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {
	$selected_values = (array) rgar( gp_populate_anything()->get_field_values_from_request(), $field->id );
	foreach ( $choices as &$choice ) {
		if ( in_array( $choice['value'], $selected_values, true ) ) {
			$choice['isSelected'] = true;
		}
	}
	return $choices;
}, 10, 3 );
