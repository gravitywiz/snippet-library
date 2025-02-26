<?php
/**
 * Gravity Wiz // Shortcode: Entry Count // Display a Filterable Entry Count
 * https://gravitywiz.com/
 *
 * This shortcode displays the count of entries for a specified Gravity Form, with various filtering options.
 *
 * Shortcode Attributes:
 * - id (int): The ID of the Gravity Form.
 * - field_id (int|string): The ID of the field to filter by.
 * - value (mixed): The value to filter the field by. Supports boolean values ('true', 'false') and merge tags.
 * - field_filters (string): A comma-separated list of field filters in the format field_id:operator:value.
 * - field_filters_mode (string): The mode for field filters. Accepts 'all' or 'any'. Default is 'all'.
 * - format (string): The format for the output number. Accepts 'decimal' or 'comma'.
 * - start_date (string): The start date for filtering entries.
 * - end_date (string): The end date for filtering entries.
 * - current_user (bool): Whether to filter entries by the current user.
 * - display_min (int): The minimum number of entries to display.
 * - display_min_alt_text (string): The text to display if the entry count is less than display_min.
 *
 * Example Usage:
 * ```
 * [gform_shortcode_entry_count id="1" field_id="2" value="example" field_filters="3:is:example,4:>5" field_filters_mode="any" format="comma" start_date="2023-01-01" end_date="2023-12-31" current_user="true" display_min="10" display_min_alt_text="Less than 10 entries"]
 * ```
 */
add_filter( 'gform_shortcode_entry_count', function( $output, $atts ) {

	$atts = shortcode_atts( array(
		'id'                   => false,
		'field_id'             => false,
		'value'                => false,
		'field_filters'        => array(), // Format: field_id:operator:value,field_id:operator:value
		'field_filters_mode'   => 'all',   // Accepts: all|any
		'format'               => false,
		'start_date'           => false,
		'end_date'             => false,
		'current_user'         => false,
		'display_min'          => 0,
		'display_min_alt_text' => '',
	), $atts );

	$value = $atts['value'];

	// Replace true/false string values with their boolean equivalent.
	if ( strtolower( $value ) === 'true' ) {
		$value = true;
	} elseif ( strtolower( $value ) === 'false' ) {
		$value = false;
	} elseif ( GFCommon::has_merge_tag( $value ) ) {
		// @todo Consider adding support for all merge tags.
		$value = GFCommon::replace_variables_prepopulate( $value );
	}

	$args = array(
		'status' => 'active',
	);

	if ( $atts['field_filters'] ) {

		$args['field_filters'] = array();

		$filter_pairs = explode(',', $atts['field_filters']); // Split by comma
		foreach ($filter_pairs as $pair) {
			$parts = explode(':', $pair);

			if (count($parts) === 3) {
				list($field_id, $operator, $value) = $parts;
			} elseif (count($parts) === 2) {
				// Default to "is" if operator is missing
				list($field_id, $value) = $parts;
				$operator = 'is';
			} else {
				// Invalid format, skip
				continue;
			}

			// Ensure values are trimmed
			$args['field_filters'][] = [
				'key'      => trim($field_id),
				'operator' => trim($operator),
				'value'    => trim($value)
			];

			$args['field_filters']['mode'] = rgar( $atts, 'field_filters_mode' );

		}

	} else if ( $atts['field_id'] ) {
		$args['field_filters'] = array(
			array(
				'key'   => $atts['field_id'],
				'value' => $value,
			),
		);
	}

	if ( $atts['start_date'] ) {
		$args['start_date'] = $atts['start_date'];
	}

	if ( $atts['end_date'] ) {
		$args['end_date'] = $atts['end_date'];
	}

	if ( $atts['current_user'] ) {
		$args['field_filters'][] = array(
			'key'   => 'created_by',
			'value' => get_current_user_id(),
		);
	}

	$entries = GFAPI::get_entries(
		$atts['id'],
		$args,
		null,
		null,
		$total_count
	);

	$output = $total_count;

	if ( $atts['display_min'] > 0 && $output < $atts['display_min'] ) {
		$output = $atts['display_min_alt_text'];
	} elseif ( $atts['format'] ) {
		$format = $atts['format'] === 'decimal' ? '.' : ',';
		$output = number_format( $output, 0, false, $format );
	}

	return $output;
}, 10, 2 );
