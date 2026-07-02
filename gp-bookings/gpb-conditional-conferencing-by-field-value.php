<?php
/**
 * Gravity Perks // Bookings // Conditional Conferencing by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Only add conferencing (e.g. the Google Meet link) to a booking's Google Calendar event when a
 * chosen field matches an allowed value.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the three settings below to match your form.
 */
add_filter( 'gpb_google_calendar_event_data', function( $event_data, $booking ) {

	// Configuration
	$form_id      = 123;            // Update '123' to your Form ID
	$field_id     = '4';            // Field ID that controls conferencing. Use a sub-field ID (e.g. '4.1') for multi-input fields.
	$field_values = array( 'Yes' ); // Update to the field value(s) that should add conferencing.

	if ( empty( $event_data['conferenceData'] ) ) {
		return $event_data;
	}

	$entry = $booking->get_entry();
	if ( ! $entry ) {
		return $event_data;
	}

	if ( (int) rgar( $entry, 'form_id' ) !== $form_id ) {
		return $event_data;
	}

	if ( ! in_array( (string) rgar( $entry, $field_id ), $field_values, true ) ) {
		unset( $event_data['conferenceData'] );
	}

	return $event_data;
}, 10, 2 );
