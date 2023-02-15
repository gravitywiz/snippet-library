<?php
/**
 * Gravity Wiz // Gravity Forms // Filter Progress Meter using field value or Date Range.
 * https://gravitywiz.com/gravity-forms-progress-meter/
 *
 * Adds support to get the count of entries with a specific value or/and within a specific date range.
 */

add_filter( 'shortcode_atts_gf_progress_meter', function( $atts ) {

	if ( $atts['name'] === 'field_filter' ) {
		// Update this array to the ID of the field and the value to check for.
		$field = array(
			'id'    => 4,
			'value' => 'First Choice',
		);
		// Update this array with the start date and end date date in this format mm/dd/yyyy.
		$date_range = array(
			'start_date' => '02/11/2023',
			'end_date'   => '02/16/2023',
		);

		$search_criteria = array(
			'status' => 'active',
		);

		if ( isset( $field ) ) {
			$search_criteria['field_filters'] = array(
				'mode' => 'all',
				array(
					'key'   => $field['id'],
					'value' => $field['value'],
				),
			);
		}

		if ( isset( $date_range ) ) {
			$search_criteria['start_date'] = date( 'Y-m-d', strtotime( $date_range['start_date'] ) );
			$search_criteria['end_date']   = date( 'Y-m-d', strtotime( $date_range['end_date'] ) );
		}

		$results       = GFAPI::get_entries( $atts['id'], $search_criteria );
		$atts['count'] = count( $results );

	}
	return $atts;
} );

