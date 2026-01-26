<?php
/**
 * Gravity Perks // Bookings // Dynamic Capacity by Time + Day of Week
 * https://gravitywiz.com/documentation/gp-bookings/
 *
 * Adjust capacity per service using day-of-week, time ranges, and optional date ranges.
 */

class GPB_Capacity_Limit_By_Time_And_Day {

	private $service_ids = array();

	private $rules = array();

	public function __construct( array $args ) {
		$this->service_ids = isset( $args['service_ids'] ) ? array_map( 'intval', (array) $args['service_ids'] ) : array();
		$this->rules       = isset( $args['rules'] ) ? (array) $args['rules'] : array();

		add_filter( 'gpb_capacity_limit', array( $this, 'filter_capacity' ), 10, 4 );
	}

	public function filter_capacity( $capacity, $start_datetime, $end_datetime, $bookable ) {
		if ( ! $bookable instanceof \GP_Bookings\Service ) {
			return $capacity;
		}

		if ( ! empty( $this->service_ids ) && ! in_array( (int) $bookable->get_id(), $this->service_ids, true ) ) {
			return $capacity;
		}

		if ( empty( $this->rules ) ) {
			return $capacity;
		}

		$start         = \GP_Bookings\Utils\DateTimeUtils::parse( $start_datetime );
		$start_minutes = ( (int) $start->format( 'H' ) * 60 ) + (int) $start->format( 'i' );
		$start_day     = strtolower( substr( $start->format( 'D' ), 0, 3 ) );
		$start_date    = $start->format( 'Y-m-d' );

		foreach ( $this->rules as $rule ) {
			if ( ! isset( $rule['capacity'] ) ) {
				continue;
			}

			$rule_start_date = isset( $rule['start_date'] ) ? trim( (string) $rule['start_date'] ) : '';
			$rule_end_date   = isset( $rule['end_date'] ) ? trim( (string) $rule['end_date'] ) : '';

			if ( $rule_start_date !== '' && $start_date < $rule_start_date ) {
				continue;
			}

			if ( $rule_end_date !== '' && $start_date > $rule_end_date ) {
				continue;
			}

			if ( ! empty( $rule['days'] ) ) {
				$rule_days = array_map( 'strtolower', (array) $rule['days'] );
				if ( ! in_array( $start_day, $rule_days, true ) ) {
					continue;
				}
			}

			$start_value = isset( $rule['start'] ) ? trim( (string) $rule['start'] ) : '';
			$end_value   = isset( $rule['end'] ) ? trim( (string) $rule['end'] ) : '';

			if ( $start_value !== '' && $end_value !== '' ) {
				$range_start = $this->time_to_minutes( $start_value );
				$range_end   = $this->time_to_minutes( $end_value );

				$in_range = ( $range_end > $range_start )
					? ( $start_minutes >= $range_start && $start_minutes < $range_end )
					: ( $start_minutes >= $range_start || $start_minutes < $range_end );
			} else {
				$in_range = true;
			}

			if ( $in_range ) {
				$capacity_value = (int) $rule['capacity'];
				if ( -1 !== $capacity_value && $capacity_value < 1 ) {
					continue;
				}

				return \GP_Bookings\Capacity\Capacity_Limit::from_int( $capacity_value );
			}
		}

		return $capacity;
	}

	private function time_to_minutes( $time ) {
		$parts = array_map( 'intval', explode( ':', (string) $time ) );
		return ( $parts[0] * 60 ) + ( $parts[1] ?? 0 );
	}
}

# Configuration

new GPB_Capacity_Limit_By_Time_And_Day( array(
	'service_ids' => array( 123 ),
	'rules'       => array(
		array(
			'start'    => '08:00',
			'end'      => '17:00',
			'capacity' => 4,
			'days'       => array( 'mon', 'tue', 'wed', 'thu', 'fri' ),
		),
		array(
			'start'    => '10:00',
			'end'      => '16:00',
			'capacity' => 2,
			'days'     => array( 'sat', 'sun' ),
		),
	),
) );

// Example: seasonal hours for a date range.
// new GPB_Capacity_Limit_By_Time_And_Day( array(
// 	'service_ids' => array( 123 ),
// 	'rules'       => array(
// 		array(
// 			'start'      => '09:00',
// 			'end'        => '18:00',
// 			'capacity'   => 6,
// 			'start_date' => '2026-06-01',
// 			'end_date'   => '2026-06-07',
// 		),
// 	),
// ) );

// Example: date-specific override (place before general rules so it wins).
// new GPB_Capacity_Limit_By_Time_And_Day( array(
// 	'service_ids' => array( 123 ),
// 	'rules'       => array(
// 		array(
// 			'start'      => '08:00',
// 			'end'        => '17:00',
// 			'capacity'   => 4,
// 			'start_date' => '2026-03-29',
// 			'end_date'   => '2026-03-31',
// 		),
// 		array(
// 			'start'    => '08:00',
// 			'end'      => '17:00',
// 			'capacity' => 2,
// 		),
// 	),
// ) );
