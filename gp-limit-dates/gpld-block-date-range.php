<?php
/**
 * Gravity Perks // GP Limit Dates // Block Date Range via Exceptions
 */
add_filter( 'gpld_limit_dates_options_1364_1', 'gpld_except_date_range', 10, 3 );
function gpld_except_date_range( $options, $form, $field ) {

	$start_date = '2016-07-15';
	$end_date   = '2017-01-01';

	// do not modify below this line

	$start_date = new DateTime( $start_date );
	$end_date   = new DateTime( $end_date );
	$period     = new DatePeriod( $start_date, new DateInterval( 'P1D' ), $end_date );

	foreach( $period as $date ) {
		array_push( $options['exceptions'], $date->format( 'm/d/Y' ) );
	}

	$options['exceptionMode'] = 'disable';

	return $options;
}