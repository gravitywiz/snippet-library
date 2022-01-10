<?php
/**
 * Gravity Perks // Limit Dates // Yearly Recurring Exceptions
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Instruction Video: https://www.loom.com/share/8552ee47135c4a8cb78560abeacdb665
 *
 * This snippet will make all excepted dates recur yearly.
 */
// Update "123" to your form ID. Update "4" to your Date field ID.
add_filter( 'gpld_limit_dates_options_123_4', function( $field_options, $form, $field ) {

	$exceptions = $field_options['exceptions'];
	if ( empty( $exceptions ) ) {
		return $field_options;
	}

	$future_exceptions = array();

	foreach ( $exceptions as $exception ) {

		$current     = new DateTime( $exception );
		$year        = new DateInterval( 'P1Y' );
		$target_year = (int) $current->format( 'Y' ) + 20;

		while ( true ) {
			$current->add( $year );
			$future_exceptions[] = $current->format( 'm/d/Y' );
			if ( (int) $current->format( 'Y' ) === $target_year ) {
				break;
			}
		}
	}

	$field_options['exceptions'] = array_merge( $exceptions, $future_exceptions );

	return $field_options;
}, 10, 3 );
