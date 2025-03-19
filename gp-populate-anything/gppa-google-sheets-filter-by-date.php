<?php
/**
 * Gravity Perks // Populate Anything + Google Sheets // Convert Date Format in Date Field for Search to m/d/y
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Experimental Snippet 🧪
 * https://gravitywiz.com/populate-gravity-forms-with-google-sheets/
 */
add_filter( 'gppa_replace_filter_value_variables_google_sheet', function ( $filter_value ) {
	if ( is_array( $filter_value ) && rgar( $filter_value, 'year' ) && rgar( $filter_value, 'month' ) && rgar( $filter_value, 'day' ) ) {
		$filter_value = sprintf( '%s/%s/%s', $filter_value['month'], $filter_value['day'], $filter_value['year'] );
	}

	return $filter_value;
}, 11 );
