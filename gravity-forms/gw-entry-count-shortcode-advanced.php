<?php
/**
 * Gravity Wiz // Shortcode: Entry Count // Display a Filterable Entry Count
 * https://gravitywiz.com/
 */
add_filter( 'gform_shortcode_entry_count', function( $output, $atts ) {

	$atts = shortcode_atts( array(
		'id'                   => false,
		'field_id'             => false,
		'value'                => false,
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
	}

	$args = array(
		'status' => 'active',
	);

	if ( $atts['field_id'] ) {
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
