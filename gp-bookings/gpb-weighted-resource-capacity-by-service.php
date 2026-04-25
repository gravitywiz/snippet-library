<?php
/**
 * Gravity Perks // Bookings // Weighted Resource Capacity by Service
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Let different services take up different amounts of the same resource.
 *
 * For example: a tennis court fits one Tennis match or two Pickleball matches at once.
 * Give the court a capacity of `2`, then set Tennis to use 2 units and Pickleball to use 1.
 * Booking Tennis fills the court on its own; up to two Pickleball bookings can share it.
 *
 * Instructions
 *
 * 1. Install: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Set the shared Resource's capacity to the total units available (e.g. `2` for two halves).
 *
 * 3. Map each Service ID to the units it consumes in `service_units`. A service's units
 *    must not exceed the resource's capacity, or it will be unbookable on that resource.
 *
 * 4. (Optional) `resource_ids` to scope to specific resources; `default_units` for unlisted services.
 */
class GPB_Weighted_Resource_Capacity_By_Service {

	private $service_units;
	private $resource_ids;
	private $default_units;

	public function __construct( array $args ) {
		$this->service_units = array_map( 'intval', (array) ( $args['service_units'] ?? array() ) );
		$this->resource_ids  = array_map( 'intval', (array) ( $args['resource_ids'] ?? array() ) );
		$this->default_units = max( 1, (int) ( $args['default_units'] ?? 1 ) );

		add_filter( 'gpb_capacity_limit', array( $this, 'filter_capacity' ), 10, 4 );
	}

	public function filter_capacity( $capacity, $start_datetime, $end_datetime, $bookable ) {
		if ( ! $bookable instanceof \GP_Bookings\Resource || $capacity->is_unlimited() ) {
			return $capacity;
		}

		if ( ! empty( $this->resource_ids ) && ! in_array( (int) $bookable->get_id(), $this->resource_ids, true ) ) {
			return $capacity;
		}

		$service = $bookable->get_service();
		if ( ! $service ) {
			return $capacity;
		}

		$service_units          = $this->units_for( $service->get_id() );
		list( $raw, $weighted ) = $this->usage_for( $bookable, $start_datetime, $end_datetime );

		$bookings_that_fit = (int) floor( max( 0, $capacity->to_int() - $weighted ) / $service_units );
		$adjusted          = $raw + $bookings_that_fit;

		if ( $adjusted < 1 ) {
			return $capacity;
		}

		return \GP_Bookings\Capacity\Capacity_Limit::limited( $adjusted );
	}

	private function usage_for( $resource, $start_datetime, $end_datetime ) {
		global $wpdb;

		$table   = \GP_Bookings\Database::table_bookings();
		$exclude = $this->excluded_booking_id();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT b.booking_id, b.parent_booking_id, b.quantity, p.object_id AS service_id
				FROM %i AS b
				LEFT JOIN %i AS p ON b.parent_booking_id = p.booking_id AND p.object_type = 'service'
				WHERE b.object_id = %d
				AND b.object_type = 'resource'
				AND b.start_datetime < %s
				AND b.end_datetime > %s
				AND b.status IN ('pending', 'confirmed')",
				$table,
				$table,
				$resource->get_id(),
				$end_datetime,
				$start_datetime
			)
		);

		$raw = $weighted = 0;

		foreach ( $rows as $row ) {
			if ( $exclude && ( (int) $row->booking_id === $exclude || (int) $row->parent_booking_id === $exclude ) ) {
				continue;
			}

			$quantity  = max( 1, (int) $row->quantity );
			$raw      += $quantity;
			$weighted += $this->units_for( (int) $row->service_id ) * $quantity;
		}

		return array( $raw, $weighted );
	}

	private function units_for( $service_id ) {
		return $this->service_units[ (int) $service_id ] ?? $this->default_units;
	}

	private function excluded_booking_id() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$id = $_REQUEST['excludeBookingId'] ?? $_REQUEST['exclude_booking_id'] ?? null;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return $id ? absint( wp_unslash( $id ) ) : 0;
	}
}

# Configuration

new GPB_Weighted_Resource_Capacity_By_Service( array(
	// Service ID => resource capacity units consumed by one booking quantity.
	'service_units' => array(
		14 => 2, // e.g. Tennis - consumes the full court.
		15 => 1, // e.g. Pickleball - consumes half the court.
	),
	// 'resource_ids'  => array( 5, 7 ), // Optional: limit to specific Resource IDs.
	// 'default_units' => 1,             // Optional: units consumed by services not listed above.
) );
