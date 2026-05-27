<?php
/**
 * Gravity Perks // GP Bookings // No Lead Time Restriction for Admins
 * https://gravitywiz.com/documentation/gp-bookings/
 *
 * Removes lead time restriction for users with 'manage_options' capability (typically admins) by setting the availability start to now.
 */
add_filter( 'gpb_availability_start', function( $start, $service, $resource_ids ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return $start;
	}

	// Admins: no lead time restriction.
	return \Carbon\CarbonImmutable::now();
}, 20, 3 );
