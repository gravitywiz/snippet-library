<?php
/**
 * Gravity Perks // Bookings // Minimum Quantity by Remaining Capacity
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * When a slot's remaining capacity is at or above the configured threshold,
 * the customer must book at least the minimum quantity. Once enough spots are
 * taken that remaining capacity drops below the threshold, the minimum no
 * longer applies.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of the snippet.
 */
class GPB_Minimum_Quantity_By_Capacity {

	private $service_ids;
	private $threshold;
	private $min_quantity;

	public function __construct( array $args = array() ) {
		$this->service_ids  = array_map( 'intval', (array) ( $args['service_ids'] ?? array() ) );
		$this->threshold    = (int) ( $args['threshold'] ?? 10 );
		$this->min_quantity = (int) ( $args['min_quantity'] ?? 2 );

		add_filter( 'gform_validation', array( $this, 'validate_submission' ) );
	}

	public function validate_submission( $validation_result ) {
		$form = $validation_result['form'];

		foreach ( $form['fields'] as &$field ) {
			if ( 'gpb_booking_time' !== $field->type || \GFFormsModel::is_field_hidden( $form, $field, array() ) ) {
				continue;
			}

			$slot_value = rgpost( 'input_' . $field->id );
			if ( rgblank( $slot_value ) ) {
				continue;
			}

			$service = $this->get_service_for_booking( $form, $field->gpbBookingField ?? null );
			if ( ! $service ) {
				continue;
			}

			try {
				$range = \GP_Bookings\Booking::normalize_datetime_values( $slot_value, $slot_value, $service );
			} catch ( \Throwable $e ) {
				continue;
			}

			$remaining = $service->get_remaining_capacity(
				$range['start']->toDateTimeString(),
				$range['end']->toDateTimeString()
			);
			if ( $remaining->is_unlimited() || $remaining->to_int() < $this->threshold ) {
				continue;
			}

			if ( $this->get_submitted_quantity( $form, $field->gpbBookingField ) < $this->min_quantity ) {
				$field->failed_validation  = true;
				$field->validation_message = sprintf(
					/* translators: %d: required minimum quantity */
					__( 'A minimum quantity of %d is required for this time slot.', 'gp-bookings' ),
					$this->min_quantity
				);
				$validation_result['is_valid'] = false;
			}
		}
		unset( $field );

		$validation_result['form'] = $form;

		return $validation_result;
	}

	private function get_service_for_booking( $form, $booking_field_id ) {
		if ( ! $booking_field_id ) {
			return null;
		}

		foreach ( $form['fields'] as $field ) {
			if ( 'gpb_service' !== $field->type || (string) $field->gpbBookingField !== (string) $booking_field_id ) {
				continue;
			}

			$service_id = ( $field->gpbSelectionMode ?? 'preselected' ) === 'manual'
				? (int) rgpost( 'input_' . $field->id )
				: (int) ( $field->gpbService ?? 0 );

			$applies = ! $this->service_ids || in_array( $service_id, $this->service_ids, true );

			return $service_id && $applies ? \GP_Bookings\Service::get( $service_id ) : null;
		}

		return null;
	}

	private function get_submitted_quantity( $form, $booking_field_id ) {
		$quantity_fields = \GFCommon::get_product_fields_by_type( $form, array( 'quantity' ), $booking_field_id );

		if ( $quantity_fields && ! \GFFormsModel::is_field_hidden( $form, $quantity_fields[0], array() ) ) {
			return max( 1, (int) \RGFormsModel::get_field_value( $quantity_fields[0] ) );
		}

		return max( 1, (int) rgpost( 'input_' . $booking_field_id . '.3' ) );
	}

}

# Configuration

new GPB_Minimum_Quantity_By_Capacity(
	array(
		'service_ids'  => array( 123 ), // Enter service IDs, or leave empty for all
		'threshold'    => 10, // Apply the minimum when remaining capacity is this or higher
		'min_quantity' => 2, // Minimum quantity required when the threshold is met
	)
);
