<?php
/**
 * Gravity Perks // Limit Dates // Dynamically Set Exceptions from Another Form
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Instruction Video: https://www.loom.com/share/9c3e7dfdf1a941bfb270bc7e1b30b3e2
 *
 * This snippet will dynamically populate the Limit Dates exception dates for a Date field on Form A from the entry data
 * for a Date field on Form B.
 */
// Update "123" to your target form ID and "4" to your target field ID.
add_filter( 'gpld_limit_dates_options_123_4', function( $field_options, $form, $field ) {

	if ( ! isset( $field_options['exceptions'] ) || ! is_array( $field_options['exceptions'] ) ) {
		$field_options['exceptions'] = array();
	}

	// Update "124" to the ID of the form from which the exceptions should be pulled.
	$source_form_id = 124;

	// Update "5" to the ID of the field on the source form from which the exceptions should be pulled.
	$source_field_id = 5;

	$field_options['exceptionMode'] = 'disable';

	$query_args = array(
		'field_filters' => array(
			array(
				'key'      => $source_field_id,
				'value'    => gmdate( 'Y-m-d' ),
				'operator' => '>=',
			),
		),
	);

	$paging = array(
		'offset'    => 0,
		'page_size' => 50,
	);

	$entries = GFAPI::get_entries( $source_form_id, $query_args, null, $paging );

	foreach ( $entries as $entry ) {
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$field_options['exceptions'][] = date( 'm/d/Y', strtotime( rgar( $entry, $source_field_id ) ) );
	}

	return $field_options;
}, 10, 3 );
