<?php
/**
 * Gravity Perks // Bookings // Cancellation Deadline by Date Booked
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Only allow cancellations for a number of days after the booking was made — e.g. a 14-day
 * cooling-off window after purchase.
 *
 * Combine with the built-in "Cancellation Deadline" setting (global or per service) to also block
 * cancellations close to the booking itself. Whichever deadline comes first wins.
 */
add_filter( 'gpb_booking_operation_restrictions', function( $restrictions, $operation, $booking ) {

	// Configuration
	$days_after_booking = 14;
	$operations         = array( 'cancel' ); // Add 'reschedule' to apply the same deadline to rescheduling.
	$service_ids        = array( 123 );      // Leave empty to apply to all services.

	if ( ! in_array( $operation, $operations, true ) || $restrictions || \GFCommon::current_user_can_any( \gpb_get_admin_capability( 'dashboard' ) ) ) {
		return $restrictions;
	}

	if ( ! empty( $service_ids ) && ! in_array( (int) $booking->get_service_id(), $service_ids, true ) ) {
		return $restrictions;
	}

	$entry = $booking->get_entry();

	if ( ! $entry ) {
		return $restrictions;
	}

	$booked   = \GP_Bookings\Utils\DateTimeUtils::to_local( $entry['date_created'] );
	$deadline = \GP_Bookings\Utils\DateTimeUtils::parse( $booked )->addDays( $days_after_booking );

	return \GP_Bookings\Utils\DateTimeUtils::now()->greaterThan( $deadline ) ? array( 'type' => 'not_allowed' ) : $restrictions;
}, 10, 3 );
