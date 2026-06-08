<?php
/**
 * Gravity Perks // Bookings // Pricing by Days of Month
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Apply pricing rules on recurring monthly dates. Rules can target:
 *
 *   - Specific days of the month, e.g. 1, 8, 15.
 *   - Day ranges, e.g. 1-7.
 *   - Days from the end of the month, e.g. -1 for the last day.
 *   - Ranges from the end of the month, e.g. -3 to -1 for the last 3 days.
 *
 * Instructions:
 *
 *   1. Install this snippet by following the steps here:
 *      https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 *   2. Update the service IDs (or resource IDs) and rules in the
 *      configuration at the end of the snippet.
 */
defined( 'ABSPATH' ) || die();

class GPB_Pricing_By_Days_Of_Month {

	private $service_ids = array();
	private $resource_ids = array();
	private $rules = array();

	public function __construct( array $args ) {
		$this->service_ids  = isset( $args['service_ids'] ) ? array_map( 'intval', (array) $args['service_ids'] ) : array();
		$this->resource_ids = isset( $args['resource_ids'] ) ? array_map( 'intval', (array) $args['resource_ids'] ) : array();
		$this->rules        = isset( $args['rules'] ) ? array_values( (array) $args['rules'] ) : array();

		add_filter( 'gpb_service_pricing_rules', array( $this, 'add_service_rules' ), 10, 3 );
		add_filter( 'gpb_resource_pricing_rules', array( $this, 'add_resource_rules' ), 10, 3 );
	}

	public function add_service_rules( $pricing_rules, $service, $booking_data ) {
		if ( ! empty( $this->service_ids ) && ! in_array( (int) $service->get_id(), $this->service_ids, true ) ) {
			return $pricing_rules;
		}

		return $this->append_rules( $pricing_rules, $booking_data );
	}

	public function add_resource_rules( $pricing_rules, $resource, $booking_data ) {
		if ( empty( $this->resource_ids ) || ! in_array( (int) $resource->get_id(), $this->resource_ids, true ) ) {
			return $pricing_rules;
		}

		return $this->append_rules( $pricing_rules, $booking_data );
	}

	private function append_rules( $pricing_rules, $booking_data ) {
		if ( empty( $this->rules ) || empty( $booking_data['start_time'] ) || empty( $booking_data['end_time'] ) ) {
			return $pricing_rules;
		}

		foreach ( $this->rules as $rule ) {
			$action = $this->build_action( $rule );

			if ( empty( $action ) || ! $this->rule_matches( $rule, $booking_data ) ) {
				continue;
			}

			$pricing_rules[] = array(
				'type'       => 'pricing_rule',
				'name'       => isset( $rule['name'] ) ? (string) $rule['name'] : __( 'Days of month pricing', 'gp-bookings' ),
				'conditions' => array(),
				'action'     => $action,
			);
		}

		return $pricing_rules;
	}

	private function rule_matches( array $rule, array $booking_data ): bool {
		$start = \GP_Bookings\Utils\DateTimeUtils::parse( $booking_data['start_time'] );

		if ( ( $rule['match_mode'] ?? 'start' ) !== 'any' ) {
			return $this->date_matches( $start, $rule );
		}

		$end = \GP_Bookings\Utils\DateTimeUtils::parse( $booking_data['end_time'] );

		for ( $current = $start->copy(); $current->lt( $end ); $current = $current->addDay() ) {
			if ( $this->date_matches( $current, $rule ) ) {
				return true;
			}
		}

		return false;
	}

	private function date_matches( $date, array $rule ): bool {
		return ! empty( $rule['days_of_month'] ) && $this->matches_days_of_month( $date, (array) $rule['days_of_month'] );
	}

	private function matches_days_of_month( $date, array $days_of_month ): bool {
		$days_in_month = (int) $date->daysInMonth;
		$day           = (int) $date->day;

		foreach ( $days_of_month as $entry ) {
			$range = (array) $entry;
			$start = $this->resolve_day_of_month( (int) reset( $range ), $days_in_month );
			$end   = $this->resolve_day_of_month( (int) end( $range ), $days_in_month );

			if ( $start > $end ) {
				list( $start, $end ) = array( $end, $start );
			}

			if ( $day >= $start && $day <= $end ) {
				return true;
			}
		}

		return false;
	}

	private function resolve_day_of_month( int $day, int $days_in_month ): int {
		$resolved_day = $day < 0 ? $days_in_month + 1 + $day : $day;

		return max( 1, min( $days_in_month, $resolved_day ) );
	}

	private function build_action( array $rule ): array {
		$modifiers = array(
			'apply_per_quantity' => ! empty( $rule['per_quantity'] ),
			'apply_per_occupant' => ! empty( $rule['per_occupant'] ),
		);

		if ( isset( $rule['set_price'] ) ) {
			return array_merge( array( 'type' => 'set_price', 'base_price' => (float) $rule['set_price'] ), $modifiers );
		}

		if ( isset( $rule['add'] ) ) {
			return array_merge( array( 'type' => 'addition', 'amount' => (float) $rule['add'] ), $modifiers );
		}

		if ( isset( $rule['multiply'] ) ) {
			return array( 'type' => 'multiplication', 'multiplier' => (float) $rule['multiply'] );
		}

		return array();
	}
}

# Configuration

new GPB_Pricing_By_Days_Of_Month( array(
	'service_ids' => array( 123 ),
	// 'resource_ids' => array(  ),
	'rules'       => array(
		array(
			'name'          => 'Days 8, 15 and 23',
			'days_of_month' => array( 8, 15, 23 ),
			'set_price'     => 100,
		),
		array(
			'name'          => 'Days 1-7',
			'days_of_month' => array( array( 1, 7 ) ),
			'set_price'     => 120,
		),
		array(
			'name'          => 'Last day of the month',
			'days_of_month' => array( -1 ),
			'add'           => 25,
		),
		array(
			'name'          => 'Last 3 days of the month',
			'days_of_month' => array( array( -3, -1 ) ),
			'multiply'      => 1.15,
		),
	),
) );
