<?php
/**
 * Gravity Perks // GP Limit Dates // Block Date Ranges via Exceptions
 */
add_filter( 'gpld_limit_dates_options_FORMID_FIELDID', 'gpld_except_date_ranges', 10, 3 );
function gpld_except_date_ranges( $options, $form, $field ) {
	/**
	 * Format: Start Date, End Date
	 */
	$ranges = array(
		array( '2016-07-15', '2017-01-01' ),
		array( '2020-12-10', '2020-12-20' ),
	);

	// do not modify below this line
	foreach ( $ranges as $range ) {
		$start_date = new DateTime( $range[0] );
		$end_date   = new DateTime( $range[1] );
		// include end date.
		$end_date->setTime( 0, 0, 1 );
		$period = new DatePeriod( $start_date, new DateInterval( 'P1D' ), $end_date );

		foreach ( $period as $date ) {
			$options['exceptions'][] = $date->format( 'm/d/Y' );
		}
	}

	$options['exceptionMode'] = 'disable';

	return $options;
}
