<?php
/**
 * Gravity Perks // Bookings // Dynamic Capacity by Time of Day
 * https://gravitywiz.com/documentation/gp-bookings/
 *
 * Automatically set different capacities based on the time of day.
 */
add_filter( 'gpb_capacity_limit_for_slot', function ( $limit, $start_datetime, $end_datetime, $bookable ) {
	if ( ! $bookable instanceof \GP_Bookings\Service ) {
		return $limit;
	}

	// Optional: limit to specific service IDs (leave empty for all services)
	$service_ids = array( 123 );
	if ( ! empty( $service_ids ) && ! in_array( (int) $bookable->get_id(), $service_ids, true ) ) {
		return $limit;
	}

	// Configure time-based capacity rules
	$time_capacity_rules = array(
		array(
			'start'    => '08:00',
			'end'      => '12:00',
			'capacity' => 2,
		),
		array(
			'start'    => '12:00',
			'end'      => '17:00',
			'capacity' => 1,
		),
		array(
			'start'    => '17:00',
			'end'      => '20:00',
			'capacity' => 3,
		),
	);

	$start         = \GP_Bookings\Utils\DateTimeUtils::parse( $start_datetime );
	$start_minutes = (int) $start->format( 'H' ) * 60 + (int) $start->format( 'i' );

	$time_to_minutes = function ( $time ) {
		$parts = array_map( 'intval', explode( ':', $time ) );
		return ( $parts[0] * 60 ) + ( $parts[1] ?? 0 );
	};

	foreach ( $time_capacity_rules as $rule ) {
		if ( empty( $rule['start'] ) || empty( $rule['end'] ) || ! isset( $rule['capacity'] ) ) {
			continue;
		}

		$range_start = $time_to_minutes( $rule['start'] );
		$range_end   = $time_to_minutes( $rule['end'] );

		$in_range = ( $range_end > $range_start )
			? ( $start_minutes >= $range_start && $start_minutes < $range_end )
			: ( $start_minutes >= $range_start || $start_minutes < $range_end );

		if ( $in_range ) {
			return \GP_Bookings\Capacity\Capacity_Limit::from_int( (int) $rule['capacity'] );
		}
	}

	return $limit;
}, 10, 4 );
