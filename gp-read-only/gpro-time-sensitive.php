<?php
/**
 * Gravity Perks // Read Only // Time-sensitive Read Only Fields
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_render_123', 'gpro_set_readonly_after_datetime' );
add_filter( 'gform_pre_process_123', 'gpro_set_readonly_after_datetime' );
function gpro_set_readonly_after_datetime( $form ) {

	// Set your desired date and time (including timezone) at which time-sensitive fields will become readonly.
	$datetime  = '2022-09-09 14:00:00 EST';
	// Specify the IDs of all fields that should be marked as readonly after the above date and time.
	$field_ids = array( 1, 2, 3 );

	if ( new DateTime( substr( $datetime, -3, 3 ) ) < new DateTime( $datetime ) ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {
		if ( in_array( $field->id, $field_ids ) ) {
			$field->gwreadonly_enable = true;
		}
	}

	return $form;
}
