<?php
/**
 * Gravity Perks // Populate Anything // Sort by Time Field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Sort times chronologically when populating a Time field from Gravity Forms entries into a choice based field.
 *
 * Usage:
 *
 * 1. Install this code as a plugin or as a snippet.
 * 2. Change your form and field ID below
 */
// Change `123` to your form ID and `4` to your populated field ID
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects, $field_values ){

	usort( $choices, function( $a, $b ) {
		$timeA = strtotime( $a['text'] );
		$timeB = strtotime( $b['text'] );
		return $timeA - $timeB;
	});

	return $choices;

}, 10, 4 );
