<?php
/**
 * Gravity Perks // Bookings // Set Admin Calendar Event Color by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * This snippet allows you to customize admin calendar event colors for individual bookings
 * based on field value or entry meta key. Map multiple values to your preferred HEX colors.
 */
add_filter( 'gpb_admin_calendar_event', function ( $event, $booking ) {

	$form_id  = 123; // Replace '123' with your Form ID.
	$field_id = '4'; // Replace with your field ID or entry meta key (e.g., 'payment_status').

	// Map field values to hex colors
	$colors = array(
		'value1' => '#fcd34d',
		'value2' => '#93c5fd',
		'value3' => '#fda4af',
	);

	if ( (int) ( $event['formId'] ?? 0 ) !== $form_id ) {
		return $event;
	}

	$entry = $event['entry'] ?? array();
	$value = $entry[ $field_id ] ?? '';

	if ( isset( $colors[ $value ] ) ) {
		$event['color'] = $colors[ $value ];
	}

	return $event;
}, 10, 2 );
