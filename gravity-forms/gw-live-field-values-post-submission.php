<?php
/**
 * Gravity Wiz // Gravity Forms // Live Field Values Post-submission
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gform_get_input_value_123_4', function( $value, $entry ) {

	static $values, $processing;

	if ( $processing ) {
		return $value;
	}

	if ( ! is_array( $values ) ) {
		$values = array();
	}

	if ( isset( $values[ $entry['id'] ] ) ) {
		return $values[ $entry['id'] ];
	}

	$processing = true;

	// Annoyingly, we have to fetch the full entry from the database as only a psuedo-entry is passed on some contexts.
	$entry        = GFAPI::get_entry( $entry['id'] );
	$date_created = new DateTime( rgar( $entry, 'date_created' ) );
	$years_of_exp = (int) rgar( $entry, 3 );

	$calc_years_of_exp      = (int) ( new DateTime() )->format( 'Y' ) - (int) $date_created->format( 'Y' ) + $years_of_exp;
	$values[ $entry['id'] ] = $calc_years_of_exp;

	$processing = false;

	return $calc_years_of_exp;
}, 10, 2 );
