<?php
/**
 * Gravity Perks // Bookings // Dynamic Block Size by Date
 * https://gravitywiz.com/documentation/gp-bookings/
 *
 * Dynamically adjust booking time slot block sizes based on configurable rules.
 * Rules can target specific days of the week, date ranges, or both.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update service IDs and rules in the configuration at the end of snippet.
 */
class GPB_Block_Size_By_Date {

	private $service_ids = array();
	private $rules       = array();

	public function __construct( array $args ) {
		$this->service_ids = isset( $args['service_ids'] ) ? array_map( 'intval', (array) $args['service_ids'] ) : array();
		$this->rules       = isset( $args['rules'] ) ? (array) $args['rules'] : array();
		add_filter( 'gpb_block_size', array( $this, 'filter_block_size' ), 10, 3 );
	}

	public function filter_block_size( $block_size, $date, $service ) {
		if ( ! $service instanceof \GP_Bookings\Service ) {
			return $block_size;
		}
		if ( ! empty( $this->service_ids ) && ! in_array( (int) $service->get_id(), $this->service_ids, true ) ) {
			return $block_size;
		}

		$day = strtolower( date( 'D', strtotime( $date ) ) );

		foreach ( $this->rules as $rule ) {
			if ( ! isset( $rule['block_size'] ) ) {
				continue;
			}
			if ( ! empty( $rule['start_date'] ) && $date < $rule['start_date'] ) {
				continue;
			}
			if ( ! empty( $rule['end_date'] ) && $date > $rule['end_date'] ) {
				continue;
			}
			if ( ! empty( $rule['days'] ) ) {
				$rule_days = array_map( 'strtolower', (array) $rule['days'] );
				if ( ! in_array( $day, $rule_days, true ) ) {
					continue;
				}
			}
			return (int) $rule['block_size'];
		}

		return $block_size;
	}
}

# Configuration

// Example: 45-minute blocks on weekends.
new GPB_Block_Size_By_Day( array(
	'service_ids' => array( 123 ),
	'rules'       => array(
		array(
			'block_size' => 45,
			'days'       => array( 'sat', 'sun' ),
		),
	),
) );

// Example: 30-minute blocks during specific date-range
// new GPB_Block_Size_By_Day( array(
//     'service_ids' => array( 456 ),
//     'rules'       => array(
//         array(
//             'block_size'  => 30,
//             'start_date'  => '2026-06-01',
//             'end_date'    => '2026-08-31',
//         ),
//     ),
// ) );
