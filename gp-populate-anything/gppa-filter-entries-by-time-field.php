<?php
/**
 * Gravity Perks // Populate Anything // Filter Entries by Time Field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Experimental Snippet 🧪
 *
 * Filter entry choices by a Time field on the source form. For example, if your source form is used
 * to collect events, including their start date and time, use this snippet to only populate events
 * that have yet to start in your target form.
 */
// Update "123" to your form ID and "4" to the field being populated with choices.
add_filter( 'gppa_input_choices_123_4', function ( $choices, $field, $objects ) {

	// Update "5" to the Time field ID on your source form.
	$time_field_id = 5;

	$new_choices = array();

	foreach ( $choices as $choice ) {
		// Convert GF Time field time (e.g. 12:00 pm) to timestamp.
		$choice_time = new DateTime( $choice['object']->$time_field_id, wp_timezone() );
		if ( $choice_time > current_datetime() ) {
			$new_choices[] = $choice;
		}
	}

	if ( empty( $new_choices ) ) {
		$new_choices[] = array(
			'text'       => 'No items available.',
			'value'      => '',
			'isSelected' => false,
		);
	}

	return $new_choices;
}, 10, 3 );
