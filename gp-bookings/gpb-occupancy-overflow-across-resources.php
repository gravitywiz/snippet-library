<?php
/**
 * Gravity Perks // Bookings // Occupancy Overflow Across Resources
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * When a booking's occupancy exceeds a single resource's maximum occupancy, automatically
 * book additional resources to fit the whole party. For example, hotel rooms with a max
 * occupancy of 8: a party of 10 books two rooms.
 *
 * Requirements:
 *   - The Resource field must use *automatic* allocation.
 *   - The maximum occupancy limit must be set on each *resource*.
 *   - The *service* must have NO maximum occupancy (rooms carry the limit).
 */
class GPB_Occupancy_Overflow {

	protected $service_ids;

	public function __construct( $args = array() ) {
		$this->service_ids = ! empty( $args['service_ids'] )
			? array_map( 'intval', (array) $args['service_ids'] )
			: array();

		add_filter( 'gpb_allocated_resources', array( $this, 'select_resources' ), 10, 5 );
		add_filter( 'gpb_resource_booking_occupancy', array( $this, 'split_occupancy' ), 10, 6 );
	}

	protected function applies( $service ) {
		if ( ! $service instanceof \GP_Bookings\Service ) {
			return false;
		}

		return empty( $this->service_ids )
			|| in_array( (int) $service->get_id(), $this->service_ids, true );
	}

	public function select_resources( $selected, $allocated, $service, $field, $context ) {
		if ( ! $this->applies( $service ) ) {
			return $selected;
		}

		$occupancy = isset( $context['occupancy'] ) ? (int) $context['occupancy'] : 0;
		if ( $occupancy < 1 || empty( $allocated ) ) {
			return $selected;
		}

		$rooms     = array();
		$remaining = $occupancy;

		foreach ( $allocated as $resource ) {
			$max = (int) $resource->get_max_occupancy();

			// A resource with no per-room limit can hold the entire party.
			if ( $max < 1 ) {
				return array( $resource );
			}

			$rooms[]    = $resource;
			$remaining -= $max;

			if ( $remaining <= 0 ) {
				break;
			}
		}

		// Not enough rooms are available for the whole party. Fall back to the default
		// selection so core rejects the booking (occupancy exceeds a single room's max).
		if ( $remaining > 0 ) {
			return $selected;
		}

		return $rooms;
	}

	public function split_occupancy( $occupancy, $resource, $index, $resources, $service, $context ) {
		// Only split when a party is actually spread across multiple resources.
		if ( ! $this->applies( $service ) || count( $resources ) <= 1 ) {
			return $occupancy;
		}

		$total = (int) $occupancy;

		// People already assigned to earlier (higher-ranked) rooms.
		$assigned_before = 0;
		foreach ( $resources as $i => $r ) {
			if ( $i >= $index ) {
				break;
			}
			$assigned_before += (int) ( $r->get_max_occupancy() ?: $total );
		}

		$room_max = (int) ( $resource->get_max_occupancy() ?: $total );

		return max( 0, min( $total - $assigned_before, $room_max ) );
	}
}

# Configuration

new GPB_Occupancy_Overflow( array(
	'service_ids' => array(), // Leave empty to apply to all services.
) );
