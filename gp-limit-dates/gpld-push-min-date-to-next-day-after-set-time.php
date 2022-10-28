<?php
/**
 * Gravity Perks // Limit Dates // Push Minimum Date to Next Day After Set Time
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * If you've configured your Date field's minimum date to "Current Date", use this snippet to set the minimum date to
 * the next day after a set time on the current date.
 */
// Update "123" to your form ID and "4" to your Date field ID.
add_filter( 'gpld_limit_dates_options_123_4', function( $options ) {
	// Update "17" to the hour (in 24-hour format) at which the minimum date should be pushed to tomorrow.
	$cutoff_hour  = 17; // (e.g. 5pm)
	$current_time = new DateTime( wp_timezone_string() );
	$cutoff_time  = ( new DateTime( wp_timezone_string() ) )->setTime( $cutoff_hour, 0 );
	if ( $current_time > $cutoff_time ) {
		$options['minDate'] = date( 'm/d/Y', strtotime( 'midnight tomorrow', $current_time->getTimestamp() ) );
	}
	return $options;
} );
