<?php
/**
 * Gravity Perks // Notification Scheduler // Add other fields to the Date Schedule
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 */
// Replace 131 in 'gpns_date_fields_131' with your form ID, and replace 5 in '$field->id == 5' with the field ID to add.
add_filter( 'gpns_date_fields_131', 'add_field_to_ns', 10, 2 );
function add_field_to_ns( $fields, $form ) {

	// loop over the form fields
	foreach ( $form['fields'] as $field ) {

		// find your target field (to add to gpns)
		if ( $field->id == 5 ) {
			// add the target field to fields array
			array_push( $fields, $field );
		}
	}

	return $fields;
}
