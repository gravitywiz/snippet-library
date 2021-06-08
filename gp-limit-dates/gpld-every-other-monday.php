<?php
/**
 * Gravity Perks // Limit Dates // Only Allow Every Other Monday
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * This snippet will block all dates except the every other Monday starting January 10th, 2022.
 */
// Update "123" to your form ID. Update "4" to your Date field ID.
add_filter( 'gpld_limit_dates_options_123_4', function( $field_options, $form, $field ) {

	$exceptions = array();

	$current     = new DateTime( '2022-01-10' );
	$week        = new DateInterval( 'P2W' );
	$target_year = (int) $current->format( 'Y' ) + 20;

	while ( true ) {
		$current->modify( 'mon this week' );
		$exceptions[] = $current->format( 'm/d/Y' );
		$current->add( $week );
		if ( (int) $current->format( 'Y' ) === $target_year ) {
			break;
		}
	}

	$field_options['disableAll']    = true;
	$field_options['exceptionMode'] = 'enable';
	$field_options['exceptions']    = $exceptions;

	return $field_options;
}, 10, 3 );
