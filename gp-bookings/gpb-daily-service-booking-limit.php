<?php
/**
 * Gravity Perks // GP Bookings // Daily Service Booking Limit
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Enforce a daily capacity for one or more booking services. When the selected services
 * meet the limit, dates are marked as unavailable in the calendar and submissions are blocked.
 * List multiple service IDs to share the cap between them.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of the snippet:
 *    - List the GP Bookings service IDs that should share the daily cap in service_ids.
 *    - Adjust daily_limit to the maximum combined bookings allowed per day.
 */
class GPB_Daily_Service_Limit {

	private $service_ids;
	private $daily_limit;

	public function __construct( array $args ) {
		$args = wp_parse_args( $args, array(
			'service_ids' => array(),
			'daily_limit' => 10,
		));

		$this->service_ids = array_map( 'intval', (array) $args['service_ids'] );
		$this->daily_limit = (int) $args['daily_limit'];

		if ( empty( $this->service_ids ) || $this->daily_limit < 1 ) {
			return;
		}

		// Guard creation and REST availability so the cap is enforced everywhere.
		add_action( 'gpb_before_booking_created', array( $this, 'guard_booking_creation' ), 10, 2 );
		add_filter( 'rest_post_dispatch', array( $this, 'filter_rest_availability' ), 10, 3 );
	}

	public function guard_booking_creation( array $booking_data, $bookable ) {
		if ( ! ( $bookable instanceof \GP_Bookings\Service ) || ! $this->is_tracked_service( $bookable->get_id() ) ) {
			return;
		}

		$date = $this->normalize_booking_date(
			$booking_data['start_datetime'] ?? '',
			$booking_data['end_datetime'] ?? ( $booking_data['start_datetime'] ?? '' ),
			$bookable
		);
		if ( ! $date ) {
			return;
		}

		$quantity = isset( $booking_data['quantity'] ) ? max( 1, (int) $booking_data['quantity'] ) : 1;

		if ( $this->exceeds_limit( array( $date ), $quantity ) ) {
			// Stop the submission when the shared limit would be exceeded.
			throw new \GP_Bookings\Exceptions\CapacityException( __( 'We are fully booked for that day. Please choose another date.', 'gp-bookings' ) );
		}
	}

	public function filter_rest_availability( $response, $server, $request ) {
		if ( ! ( $request instanceof \WP_REST_Request ) || 'GET' !== $request->get_method() ) {
			return $response;
		}

		$route = ltrim( $request->get_route(), '/' );
		if ( 'gp-bookings/v1/availability/days' !== $route ) {
			return $response;
		}

		$service_id = (int) $request->get_param( 'serviceId' );
		if ( ! $service_id || ! $this->is_tracked_service( $service_id ) ) {
			return $response;
		}

		if ( is_wp_error( $response ) || ! ( $response instanceof \WP_HTTP_Response ) ) {
			return $response;
		}

		$data = $response->get_data();
		if ( empty( $data['days'] ) || ! is_array( $data['days'] ) ) {
			return $response;
		}

		$dates = array_keys( $data['days'] );
		if ( ! $dates ) {
			return $response;
		}

		$exclude_booking_id = (int) $request->get_param( 'exclude_booking_id' );
		$exclude_booking_id = $exclude_booking_id > 0 ? $exclude_booking_id : null;

		$totals = $this->get_daily_totals( $dates, $exclude_booking_id );

		foreach ( $data['days'] as $date => &$day ) {
			if ( ( $totals[ $date ] ?? 0 ) >= $this->daily_limit ) {
				// Flag the day as unavailable in the REST response.
				$day['available']      = false;
				$day['status']         = 'booked';
				$day['remainingSlots'] = 0;
			}
		}
		unset( $day );

		$response->set_data( $data );
		return $response;
	}

	private function exceeds_limit( array $dates, int $incoming_quantity = 0, $exclude_booking_id = null ): bool {
		$dates  = array_filter( array_unique( $dates ) );
		$totals = $dates ? $this->get_daily_totals( $dates, $exclude_booking_id ) : array();

		foreach ( $dates as $date ) {
			$existing_total = $totals[ $date ] ?? 0;
			if ( $existing_total + $incoming_quantity > $this->daily_limit ) {
				return true;
			}
		}

		return false;
	}

	private function get_daily_totals( array $dates, $exclude_booking_id = null ): array {
		$dates = array_values( array_filter( array_unique( array_map( 'trim', $dates ) ) ) );
		if ( ! $dates ) {
			return array();
		}

		$start_datetime = min( $dates ) . ' 00:00:00';
		$end_datetime   = max( $dates ) . ' 23:59:59';

		return $this->get_totals_for_range( $start_datetime, $end_datetime, $exclude_booking_id );
	}

	private function get_totals_for_range( string $start_datetime, string $end_datetime, $exclude_booking_id = null ): array {
		if ( '' === $start_datetime || '' === $end_datetime ) {
			return array();
		}

		$bookings = \GP_Bookings\Queries\Booking_Query::get_bookings_in_range(
			$start_datetime,
			$end_datetime,
			array(
				'object_id'                     => $this->service_ids,
				'object_type'                   => 'service',
				'status'                        => array( 'pending', 'confirmed' ),
				'exclude_service_with_resource' => false,
				'exclude_booking_id'            => $exclude_booking_id,
			)
		);

		if ( ! $bookings ) {
			return array();
		}

		$totals = array();

		foreach ( $bookings as $booking ) {
			try {
				$service_id = (int) $booking->get_service_id();
			} catch ( \Throwable $e ) {
				continue;
			}

			if ( ! $this->is_tracked_service( $service_id ) ) {
				continue;
			}

			$service = \GP_Bookings\Service::get( $service_id );
			if ( ! $service ) {
				continue;
			}

			$date = $this->normalize_booking_date(
				$booking->get_start_datetime(),
				$booking->get_end_datetime(),
				$service
			);

			if ( ! $date ) {
				continue;
			}

			$totals[ $date ] = ( $totals[ $date ] ?? 0 ) + (int) $booking->get_quantity();
		}

		return $totals;
	}

	private function is_tracked_service( int $service_id ): bool {
		return in_array( $service_id, $this->service_ids, true );
	}

	private function normalize_booking_date( $start, $end, $bookable ): ?string {
		try {
			$normalized = \GP_Bookings\Booking::normalize_datetime_values( $start, $end, $bookable );
		} catch ( \Throwable $e ) {
			return null;
		}

		return $normalized['start']->format( 'Y-m-d' );
	}

}

# Configuration
new GPB_Daily_Service_Limit(
	array(
		'service_ids' => array( 123, 456 ), // Enter one or more service IDs
		'daily_limit' => 10,                 // Enter the daily limit
	)
);
