<?php
/**
 * Gravity Perks // Bookings // Reserved Capacity for Logged-in Users
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Reserve a portion of a service's per-slot capacity for logged-in users until a cutoff
 * number of days before the booking (e.g. on a capacity-2 slot, hold 1 spot for logged-in
 * users until 30 days out). Reserved spots are released to everyone once the cutoff is reached.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of the snippet:
 *    - List the GP Bookings service IDs that should reserve capacity in service_ids.
 *    - Adjust reserved_capacity to the number of spots per slot reserved for logged-in users.
 *    - Adjust release_days_before to the number of days before the booking date
 *      at which reserved spots are released to the general public/logged-out users.
 */
class GPB_Reserved_Capacity {

	private $service_ids;
	private $reserved_capacity;
	private $release_days_before;

	public function __construct( array $args ) {
		$args = wp_parse_args( $args, array(
			'service_ids'         => array(),
			'reserved_capacity'   => 0,
			'release_days_before' => 0,
		));

		$this->service_ids         = array_map( 'intval', (array) $args['service_ids'] );
		$this->reserved_capacity   = max( 0, (int) $args['reserved_capacity'] );
		$this->release_days_before = max( 0, (int) $args['release_days_before'] );

		if ( empty( $this->service_ids ) || $this->reserved_capacity < 1 ) {
			return;
		}

		add_filter( 'gpb_capacity_limit', array( $this, 'filter_capacity_limit' ), 10, 4 );
	}

	public function filter_capacity_limit( $capacity, $start_datetime, $end_datetime, $bookable ) {
		if ( ! ( $capacity instanceof \GP_Bookings\Capacity\Capacity_Limit ) ) {
			return $capacity;
		}

		if ( ! ( $bookable instanceof \GP_Bookings\Service ) || ! $this->is_tracked_service( (int) $bookable->get_id() ) ) {
			return $capacity;
		}

		if ( is_user_logged_in() ) {
			return $capacity;
		}

		if ( ! $this->is_within_reserved_window( $start_datetime ) ) {
			return $capacity;
		}

		if ( $capacity->is_unlimited() ) {
			return $capacity;
		}

		$reduced = max( 0, $capacity->to_int() - $this->reserved_capacity );

		return \GP_Bookings\Capacity\Capacity_Limit::limited( $reduced );
	}

	private function is_tracked_service( int $service_id ): bool {
		return in_array( $service_id, $this->service_ids, true );
	}

	private function is_within_reserved_window( string $start_datetime ): bool {
		try {
			$booking_date = \Carbon\Carbon::parse( $start_datetime )->startOfDay();
		} catch ( \Throwable $e ) {
			return false;
		}

		$cutoff = \Carbon\Carbon::now()->startOfDay()->addDays( $this->release_days_before );

		return $booking_date->greaterThan( $cutoff );
	}

}

# Configuration

new GPB_Reserved_Capacity(
	array(
		'service_ids'         => array( 123, 456 ), // Enter one or more service IDs
		'reserved_capacity'   => 1, // Spots per slot reserved for logged-in users
		'release_days_before' => 30, // Days before booking when reserved spots open to the public
	)
);
