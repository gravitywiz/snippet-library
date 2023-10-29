<?php
/**
 * Gravity Perks // Populate Anything // Dynamic Quantity Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/a60d1691b6dd4c07a3d9e65cd4fc6e5c
 *
 * Populate a dynamic number of choices for a Quantity field based on a populated value. For example, a canoe might have
 * a capacity of 2 people while a pontoon may have a capacity of 8 people. If you populate the raw capacity as a choice
 * in the Quantity field, this snippet will then convert that single choice into a dynamic list of choices from 1 to the
 * specified capacity of the selected boat.
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {

	$capacity = $choices[0]['value'];
	if ( ! $capacity ) {
		return $choices;
	}

	$choices = array();
	while( $capacity > 0 ) {
		$choices[] = array(
			'text'  => $capacity,
			'value' => $capacity,
		);
		$capacity--;
	}

	return array_reverse( $choices);
}, 10, 3 );
