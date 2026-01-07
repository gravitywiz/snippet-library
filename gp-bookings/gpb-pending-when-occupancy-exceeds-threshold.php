<?php
/**
 * Gravity Perks // Bookings // Pending When Occupancy Exceeds Threshold
 * https://gravitywiz.com/documentation/gp-bookings/
 *
 * Automatically set bookings to Pending when the occupancy exceeds a threshold.
 */
add_action( 'gpb_after_booking_created', function ( $booking, $booking_data, $object, $entry ) {

	// Configuration
	$pending_threshold = 5; // Set the pending threshold
	$target_form_ids = array( 123 ); // Leave empty to apply to all forms

	if ( ! $booking instanceof \GP_Bookings\Booking || $booking->get_type() !== 'service' ) {
		return;
	}

	if ( ! empty( $target_form_ids ) ) {
		$form_id = $entry && isset( $entry['form_id'] ) ? (int) $entry['form_id'] : 0;
		if ( ! $form_id || ! in_array( $form_id, $target_form_ids, true ) ) {
			return;
		}
	}

	$occupancy = $booking->get_occupancy();
	if ( $occupancy === null ) {
		return;
	}

	if ( $occupancy > $pending_threshold ) {
		$booking->update_status( 'pending', 'Auto-pending due to occupancy threshold', true );
	}
}, 10, 4 );
