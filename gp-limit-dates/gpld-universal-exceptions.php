<?php
/**
 * Gravity Perks // Limit Dates // Universal Date Exceptions
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Except dates across all Date fields on all forms. You can also skip applying exceptions for certain forms and fields.
 */
add_filter( 'gpld_limit_dates_options', function( $field_options, $form, $field ) {
	// Optional exclusions: add IDs to skip applying exceptions.
	$exclude = array();
	/*
	$exclude = array(
		array(
			'form_id' => 123, // Exclude all date fields on form 123.
		),
		array(
			'form_id'  => 124,
			'field_id' => 5, // Exclude field 5 on form 124.
		),
	);
	*/

	$form_id           = (int) $form['id'];
	$excluded_field_ids = array();

	foreach ( $exclude as $rule ) {
		if ( empty( $rule['form_id'] ) || (int) $rule['form_id'] !== $form_id ) {
			continue;
		}

		if ( empty( $rule['field_id'] ) ) {
			return $field_options;
		}

		$excluded_field_ids[] = (int) $rule['field_id'];
	}

	foreach ( $field_options as $field_id => &$_field_options ) {
		if ( in_array( (int) $field_id, $excluded_field_ids, true ) ) {
			continue;
		}

		if ( ! isset( $_field_options['exceptions'] ) || ! is_array( $_field_options['exceptions'] ) ) {
			$_field_options['exceptions'] = array();
		}
		// Add as many exceptions as you need here.
		$_field_options['exceptions'][] = '04/01/2026';
		$_field_options['exceptions'][] = '07/04/2026';
		$_field_options['exceptions'][] = '12/25/2026';
	}

	return $field_options;
}, 10, 3 );
