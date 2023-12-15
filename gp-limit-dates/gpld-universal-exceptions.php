<?php
/**
 * Gravity Perks // Limit Dates // Universal Exceptions
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Except dates across all Date fields on all forms.
 */
add_filter( 'gpld_limit_dates_options', function( $field_options, $form, $field ) {

	foreach ( $field_options as &$_field_options ) {
		if ( ! isset( $_field_options['exceptions'] ) || ! is_array( $_field_options['exceptions'] ) ) {
			$_field_options['exceptions'] = array();
		}
		// Add as many exceptions as you need here.
		$_field_options['exceptions'][] = '04/01/2023';
		$_field_options['exceptions'][] = '07/04/2023';
		$_field_options['exceptions'][] = '12/25/2023';
	}

	return $field_options;
}, 10, 3 );
