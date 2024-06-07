<?php
/**
 * Gravity Perks // Populate Anything // Populate Entries from Multiple Forms
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Filter and populate desired entry IDs by using the Populate Anything Database object type. Populate the choice label
 * with the "entry_id" column. Configure the snippet below to fetch the desired input values from each entry depending
 * on the form it belongs to.
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function ( $choices, $field, $objects ) {

	// Each case below represents a form ID (e.g. 121, 122) from which you are pulling entries for population.
	// You must target the field (or input IDs) that you want to populate from.
	foreach ( $choices as &$choice ) {
		$entry = GFAPI::get_entry( $choice['object']['entry_id'] );
		switch ( $entry['form_id'] ) {
			case 121:
				$first_name = $entry['2.3'];
				$last_name  = $entry['2.6'];
				break;
			case 122:
				$first_name = $entry['3.3'];
				$last_name  = $entry['3.6'];
				break;
		}
		$choice['text']  = sprintf( '%s %s', $first_name, $last_name );
		$choice['value'] = sprintf( '%s %s', $first_name, $last_name );
	}

	return $choices;
}, 10, 3 );
