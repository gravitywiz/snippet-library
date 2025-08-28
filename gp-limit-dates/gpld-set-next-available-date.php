<?php
/**
 * Gravity Perks // Limit Dates // Set Next Available Date as Default Value
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * This snippet sets the default value of specified Date fields to the next available date
 * based on their GP Limit Dates configuration.
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 *
 * 2. Add the parameter `gpld_next_available_date` to the "Allow field to be populated dynamically" setting
 *
 * 3. Profit.
 */
add_filter( 'gform_field_value_gpld_next_available_date', function( $value, $field, $name ) {

	// Only set default if field is empty
	if ( ! empty( $value ) ) {
		return $value;
	}

	// Find and return the next available date
	$next_date = gpld_find_next_available_date( $field );

	return $next_date ?: $value;

}, 10, 3 );

/**
 * Find the next available date for a field with GP Limit Dates configuration
 * 
 * @param GF_Field $field The date field
 * @param int $start_date Optional starting date timestamp. Defaults to today.
 * @param int $max_days Maximum days to check ahead. Defaults to 365.
 * @return string|null Next available date in field format, or null if none found
 */
if ( ! function_exists( 'gpld_find_next_available_date' ) ) {
	function gpld_find_next_available_date( $field, $start_date = null, $max_days = 365 ) {
		// Check if GP Limit Dates is active
		if ( ! function_exists( 'gp_limit_dates' ) || ! gp_limit_dates()->has_limit_dates_enabled( $field ) ) {
			return null;
		}

		// Start from today if no start date provided
		if ( $start_date === null ) {
			$start_date = gp_limit_dates()->get_midnight( $field );
		}

		// Check each day starting from the start date
		for ( $i = 0; $i < $max_days; $i++ ) {
			$check_date = strtotime( "+{$i} days", $start_date );

			if ( gp_limit_dates()->is_valid_date( $check_date, $field ) ) {
				// Convert timestamp to the field's date format
				return gpld_format_date_for_field( $check_date, $field );
			}
		}

		return null;
	}
}

/**
 * Format a timestamp according to the field's date format
 * 
 * @param int $timestamp Unix timestamp
 * @param GF_Field $field The date field
 * @return string Formatted date string
 */
if ( ! function_exists( 'gpld_format_date_for_field' ) ) {
	function gpld_format_date_for_field( $timestamp, $field ) {
		$date_format = $field->dateFormat ? $field->dateFormat : 'mdy';

		switch ( $date_format ) {
			case 'mdy':
				return date( 'm/d/Y', $timestamp );
			case 'dmy':
				return date( 'd/m/Y', $timestamp );
			case 'dmy_dash':
				return date( 'd-m-Y', $timestamp );
			case 'dmy_dot':
				return date( 'd.m.Y', $timestamp );
			case 'ymd_slash':
				return date( 'Y/m/d', $timestamp );
			case 'ymd_dash':
				return date( 'Y-m-d', $timestamp );
			case 'ymd_dot':
				return date( 'Y.m.d', $timestamp );
			default:
				return date( 'm/d/Y', $timestamp ); // fallback to mdy
		}
	}
}
