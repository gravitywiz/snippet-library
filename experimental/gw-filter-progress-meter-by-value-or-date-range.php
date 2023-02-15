<?php
/**
 * Gravity Wiz // Gravity Forms // Filter Progress Meter Using Field Values or Date Range.
 * https://gravitywiz.com/gravity-forms-progress-meter/
 *
 * Adds support to get the count of entries with a specific values or/and within a specific date range.
 */

add_filter( 'shortcode_atts_gf_progress_meter', function( $atts ) {

	if ( $atts['name'] === 'field_filter' ) {

		$field_filters = array(
			// Update 'mode' to either all or any.
			'mode' => 'all',
			// Update 'key to the Field ID and 'value' to the value to check for.
			array(
				'key'   => '4',
				'value' => 'Second Choice',
			),
			array(
				'key'   => '5',
				'value' => 'Test',
			),
		);

		$date_range = array(
			'start_date' => '02/10/2023',
			'end_date'   => '02/16/2023',
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

		$results       = GFAPI::get_entries( $atts['id'], $search_criteria );
		$atts['count'] = count( $results );

	}
	return $atts;
} );

