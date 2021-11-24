<?php
/**
 * Gravity Perks // Limit Dates // Pad the Minimum Date Beyond the Current Weekend
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 */
// Update "123" to your form ID and "4" to your Date field ID.
add_filter( 'gpld_limit_dates_options_123_4', function( $field_options ) {
	$day_number = (int) date( 'N' );
	if ( $day_number >= 6 ) {
		$min_date          = $field_options['minDate'];
		$day_padding       = $day_number === 6 ? 2 : 1;
		$modified_min_date = strtotime( "+{$day_padding} days", strtotime( $min_date ) );
		if ( $min_date < $modified_min_date ) {
			$field_options['minDate'] = date( 'm/d/Y', $modified_min_date );
		}
	}
	return $field_options;
} );
