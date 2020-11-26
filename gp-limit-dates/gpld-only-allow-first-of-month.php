<?php
/**
 * Gravity Perks // Limit Dates // Only Allow First of the Month
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * This snippet will block all dates except the first date of each month.
 */
// Update "123" to your form ID. Update "4" to your Date field ID.
add_filter( 'gpld_limit_dates_options_123_4', function( $field_options, $form, $field ) {

	$exceptions = array();

	$current     = new DateTime();
	$month       = new DateInterval( 'P1M' );
	$target_year = (int) $current->format( 'Y' ) + 20;

	while ( true ) {
		$current->modify( 'first day of this month' );
		$exceptions[] = $current->format( 'm/d/Y' );
		$current->add( $month );
		if ( (int) $current->format( 'Y' ) === $target_year ) {
			break;
		}
	}

	$field_options['disableAll']    = true;
	$field_options['exceptionMode'] = 'enable';
	$field_options['exceptions']    = $exceptions;

	return $field_options;
}, 10, 3 );
