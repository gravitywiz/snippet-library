<?php
/**
 * Gravity Perks // Bookings // Block Resources from Service Timeslots
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Automatically treats a service's availability timeslots as unavailability on a
 * list of resources, so those resources are claimed the moment a timeslot is added,
 * without creating a real booking and without writing anything to the database.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of the snippet:
 *    - List the GP Bookings service IDs whose timeslots should reserve
 *      resources in service_ids.
 *    - List the resource IDs that should be blocked during those timeslots
 *      in resource_ids.
 *    - Adjust buffer_minutes to the number of minutes to block before AND
 *      after each timeslot. Set to 0 to disable buffering.
 */

class GPB_Block_Resources_From_Service_Timeslots {

	private $service_ids = array();
	private $resource_ids = array();
	private $buffer_minutes = 0;
	private $is_reading = false;

	public function __construct( array $args ) {
		$this->service_ids    = isset( $args['service_ids'] ) ? array_map( 'intval', (array) $args['service_ids'] ) : array();
		$this->resource_ids   = isset( $args['resource_ids'] ) ? array_map( 'intval', (array) $args['resource_ids'] ) : array();
		$this->buffer_minutes = isset( $args['buffer_minutes'] ) ? max( 0, (int) $args['buffer_minutes'] ) : 0;

		if ( empty( $this->service_ids ) || empty( $this->resource_ids ) ) {
			return;
		}

		add_filter( 'gpb_availability_blocks', array( $this, 'inject_blocks' ), 10, 2 );
	}

	public function inject_blocks( $blocks, $bookable ) {
		if ( $this->is_reading ) {
			return $blocks;
		}

		if ( ! $bookable instanceof \GP_Bookings\Resource ) {
			return $blocks;
		}

		if ( ! in_array( $bookable->get_id(), $this->resource_ids, true ) ) {
			return $blocks;
		}

		$this->is_reading = true;

		foreach ( $this->service_ids as $service_id ) {
			$service = \GP_Bookings\Service::get( $service_id );
			if ( ! $service ) {
				continue;
			}

			foreach ( $service->availability->get_blocks() as $source ) {
				if ( $source->is_negative() ) {
					continue;
				}

				$times = $this->apply_buffer( $source->get_start_time(), $source->get_end_time() );

				$blocks[] = new \GP_Bookings\AvailabilityBlock(
					array(
						'block_id'          => -1,
						'object_id'         => $bookable->get_id(),
						'object_type'       => 'resource',
						'availability_days' => $source->get_availability_days(),
						'start_date'        => $source->get_start_date(),
						'end_date'          => $source->get_end_date(),
						'start_time'        => $times['start_time'],
						'end_time'          => $times['end_time'],
						'description'       => sprintf( 'Synced from service %d', $service_id ),
						'is_negative'       => true,
						'recurring'         => $source->is_recurring(),
					),
					$bookable
				);
			}
		}

		$this->is_reading = false;

		return $blocks;
	}

	private function apply_buffer( ?string $start_time, ?string $end_time ): array {
		if ( $start_time === null || $end_time === null || $this->buffer_minutes === 0 ) {
			return array(
				'start_time' => $start_time,
				'end_time'   => $end_time,
			);
		}

		$start = \Carbon\Carbon::parse( $start_time )->subMinutes( $this->buffer_minutes );
		$end   = \Carbon\Carbon::parse( $end_time )->addMinutes( $this->buffer_minutes );

		return array(
			'start_time' => $start->max( $start->copy()->startOfDay() )->format( 'H:i:s' ),
			'end_time'   => $end->min( $end->copy()->endOfDay() )->format( 'H:i:s' ),
		);
	}
}

# Configuration

new GPB_Block_Resources_From_Service_Timeslots( array(
	'service_ids'    => array( 123 ), // Service(s) whose timeslots should reserve the resources below.
	'resource_ids'   => array( 42, 43 ), // Resources that get blocked for the duration of each service timeslot.
	'buffer_minutes' => 60, // Minutes to block before AND after each timeslot.
) );
