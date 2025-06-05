<?php
/**
 * Gravity Perks // Populate Anything // Preserve Field Selections on Repopulation
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {
	$field_values    = gp_populate_anything()->get_field_values_from_request();
	$selected_values = (array) rgar( $field_values, $field->id );
	if ( $field->type == 'checkbox' && ! empty( $selected_values ) ) {
		// look in field_values for values of index like 5.1, 5.2 etc. where 5 is the field id
		$selected_values = array();
		foreach ( $field_values as $key => $value ) {
			if ( strpos( (string) $key, $field->id . '.' ) === 0 ) {
				$selected_values[ $key ] = $value;
			}
		}
	}

	foreach ( $choices as &$choice ) {
		if ( in_array( $choice['value'], $selected_values, true ) ) {
			$choice['isSelected'] = true;
		}
	}

	return $choices;
}, 10, 3 );

// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_field_choices_posted_value_123_4', '__return_false' );
