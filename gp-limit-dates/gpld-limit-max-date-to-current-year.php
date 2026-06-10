<?php
/**
 * Gravity Perks // Limit Dates // Limit Max Date to Current Year
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 */
// Update "123" to your form ID and "4" to your Date field ID.
add_filter( 'gpld_limit_dates_options_123_4', function( $field_options ) {

	$field_options['maxDate'] = gmdate( '12/31/Y' );

	return $field_options;
} );
