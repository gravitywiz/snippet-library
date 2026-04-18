<?php
/**
 * Gravity Perks // Bookings // Late Booking Grace Period
 * https://gravitywiz.com/documentation/gp-bookings/
 *
 * Allow the current time slot to remain bookable for a set number of minutes after it starts,
 * as long as capacity is still available. For example, a 4:00–5:00 PM slot with a 10-minute
 * grace period can still be booked until 4:10 PM.
 *
 * Instructions
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update `$grace_period_minutes` to your desired grace window.
 *
 * 3. (Optional) Update `$enabled_service_ids` with specific service IDs to
 *    restrict the grace period to selected services. Leave empty to enable
 *    for all services.
 */
add_filter( 'rest_request_after_callbacks', function( $response, $handler, $request ) {
	// Change to 5, 10, 15, etc.
	$grace_period_minutes = 10;

	// Update to restrict the grace period to specific service IDs, e.g. array( 5, 9 ).
	// Leave empty to enable for all services.
	$enabled_service_ids = array();

	if ( $grace_period_minutes <= 0 ) {
		return $response;
	}

	if ( ! $response instanceof \WP_REST_Response ) {
		return $response;
	}

	if ( $request->get_route() !== '/gp-bookings/v1/availability/day' ) {
		return $response;
	}

	$data = $response->get_data();
	if ( ! is_array( $data ) || empty( $data['slots'] ) ) {
		return $response;
	}

	$service_id = (int) $request->get_param( 'serviceId' );

	if ( ! empty( $enabled_service_ids ) && ! in_array( $service_id, $enabled_service_ids, true ) ) {
		return $response;
	}

	$service = \GP_Bookings\Service::get( $service_id );
	if ( ! $service ) {
		return $response;
	}

	$date = $request->get_param( 'date' );
	if ( ! $date ) {
		return $response;
	}

	$resource_ids       = $request->get_param( 'resources' ) ?: array();
	$resource_mode      = $request->get_param( 'resourceMode' ) ?: 'all';
	$exclude_booking_id = $request->get_param( 'excludeBookingId' );
	$exclude_booking_id = $exclude_booking_id ? (int) $exclude_booking_id : null;

	$now        = \GP_Bookings\Utils\DateTimeUtils::now();
	$block_size = $service->get_block_size_for_date( $date );

	foreach ( $data['slots'] as &$slot ) {
		if ( empty( $slot['reason'] ) || $slot['reason'] !== 'past' ) {
			continue;
		}

		$start = \GP_Bookings\Utils\DateTimeUtils::parse( $date . ' ' . $slot['time'] );

		if ( $now->lt( $start ) || $now->gt( $start->addMinutes( $grace_period_minutes ) ) ) {
			continue;
		}

		$end = $start->addMinutes( $block_size );

		$remaining = $service->availability->get_remaining_capacity_with_resources(
			$start->format( 'Y-m-d H:i:s' ),
			$end->format( 'Y-m-d H:i:s' ),
			$resource_ids,
			$exclude_booking_id,
			$resource_mode
		);

		if ( ! $remaining->has_capacity() ) {
			continue;
		}

		$slot['available']      = true;
		$slot['remainingSlots'] = $remaining->to_api_value();
	}
	unset( $slot );

	$response->set_data( $data );

	return $response;
}, 10, 3 );
