<?php
/**
 * Gravity Perks // Read Only // Time-sensitive Read Only Fields
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 */
// Update "123" to your form ID.
// Update "4", "5", and "6" in the array to the field IDs which should be set as readonly after the given date/time.
// Specify a date/time in the 24-hour format.
gpro_set_readonly_after_datetime( 123, array( 4, 5, 6 ), '2022-09-09 16:00:00' );

function gpro_set_readonly_after_datetime( $form_id, $field_ids, $datetime ) {

	$func = function( $form ) use( $field_ids, $datetime ) {

		$current_time  = new DateTime( wp_timezone_string() );
		$readonly_time = new DateTime( $datetime . ' ' . wp_timezone_string() );

		if ( $current_time < $readonly_time ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( in_array( $field->id, $field_ids ) ) {
				$field->gwreadonly_enable = true;
			}
		}

		return $form;
	};

	add_filter( "gform_pre_render_{$form_id}", $func );
	add_filter( "gform_pre_process_{$form_id}", $func );

}
