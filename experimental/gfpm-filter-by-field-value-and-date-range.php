<?php
/**
 * Gravity Wiz // Gravity Forms // Filter Progress Meter by Field Value & Date Range
 * https://gravitywiz.com/gravity-forms-progress-meter/
 *
 * Adds support to get the count of entries with a specific values within a specific date range.
 *
 * Instructions
 *
 * 1. Install and activate the Progress Meter snippet.
 *    https://gravitywiz.com/gravity-forms-progress-meter/
 *
 * 2. Add the following code snippet to your theme's functions.php file or wherever you include custom code.
 *
 * 3. Follow the inline instructions in the snippet to configure for your form and fields.
 *
 * 4. Update your progress meter shortcode to include a name attribute with the value "my_custom_filter" (or whatever
 *    name you've set in the snippet below).
 *
 *    [gravityforms id="123" action="meter" goal="10" name="my_custom_filter"]
 */
add_filter( 'shortcode_atts_gf_progress_meter', function ( $atts ) {

	// Update "my_custom_filter" to your desired name to avoid conflicts with other progress meter shortcodes.
	if ( $atts['name'] === 'my_custom_filter' ) {

		$field_filters = array(
			// Update "all" to "any" if you would like to return results that match any filter. Leave as "all" to return
			// only return results that match all filters.
			'mode' => 'all',
			array(
				// Update "1" to the field ID in which you will search for the value.
				'key'   => '1',
				// Update "value a" to the value you want to search for.
				'value' => 'value a',
			),
			// Add as many additional field filters as you need.
			array(
				'key'   => '2',
				'value' => 'value b',
			),
		);

		// Update "02/01/2023" to the start date and "02/15/2023" to the end date of your search range. Only entries that
		// were created within this date range will be returned.
		$date_range = array(
			'start_date' => '02/01/2023',
			'end_date'   => '02/15/2023',
		);

		$search_criteria = array(
			'status' => 'active',
		);

		if ( isset( $field_filters ) ) {
			$search_criteria['field_filters'] = $field_filters;
		}

		if ( isset( $date_range ) ) {
			$search_criteria['start_date'] = gmdate( 'Y-m-d', strtotime( $date_range['start_date'] ) );
			$search_criteria['end_date']   = gmdate( 'Y-m-d', strtotime( $date_range['end_date'] ) );
		}

		// The $count is passed by reference and will be updated by the function call.
		GFAPI::get_entries( $atts['id'], $search_criteria, null, null, $count );

		$atts['count'] = $count;

	}

	return $atts;
} );
