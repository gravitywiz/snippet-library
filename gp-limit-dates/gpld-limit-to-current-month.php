<?php
/**
 * Gravity Perks // Limit Dates // Limit Dates to Current Month
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 */
// Update "123" to your form ID and "4" to your Date field ID.
add_filter( 'gpld_limit_dates_options_123_4', function( $field_options ) {

	// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	$field_options['minDate'] = date( 'm/01/Y' );

	// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	$field_options['maxDate'] = date( 'm/t/Y' );

	return $field_options;
} );
