<?php
/**
 * Gravity Perks // Date Time Calculator // Change Max Delta
 * https://gravitywiz.com/documentation/gravity-forms-date-time-calculator/
 *
 * Control the max delta that’s allowable between the frontend calculation result and the backend calculation
 * result before triggering a warning email and/or note.
 */
add_filter( 'gpdtc_warning_max_delta', function( $delta, $result, $formula, $field, $form, $entry ) {
	// Update "123" to the form ID and "4" to the ID of the Number field with the Date Time calculations.
	if ( $field->formId !== 123 || $field->id != 4 ) {
		return $delta;
	}
	// Update "0.0002" to your preferred max delta value.
	return 0.0002;
}, 10, 6 );
