<?php
/**
 * Gravity Perks // Unique ID // Reset Sequence at Specific Threshold
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Reset the sequence of a Sequential Unique ID field when the sequence reaches a specified number.
 */
add_filter( 'gpui_unique_id', function( $unique, $form_id, $field_id ) {

	// Update "123" to your form ID and "4" to the ID of your Unique ID field.
	if ( $form_id == 123 && $field_id == 4 ) {

		// Update "1" to the number the sequence should be reset to when the threshold is reached.
		$starting_number = 1;

		// Update "99" to the threshold at which the sequence should be reset.
		$reset_number = 99;

		if ( $unique == $reset_number ) {
			gp_unique_id()->set_sequential_starting_number( $form_id, $field_id, $starting_number - 1 );
		}
	}

	return $unique;
}, 10, 3 );
