<?php
/**
 * Gravity Perks // GP Bookings // Daily Service Booking Limit
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Enforce a daily capacity for one or more GP Bookings services. When the selected services
 * meet the limit, new submissions are blocked and the booking time field displays a
 * "fully booked" message for that day. List multiple service IDs to share the cap between them.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of the snippet:
 *    - Set form_id to the Gravity Form that hosts your booking field (or leave false to run on every form).
 *    - List the GP Bookings service IDs that should share the daily cap in service_ids.
 *    - Adjust daily_limit to the maximum combined bookings allowed per day.
 *    - Optionally customize capacity_message to change the validation text shown to users.
 */
class GPB_Daily_Service_Limit {

	private $form_id;
	private $service_ids;
	private $daily_limit;
	private $capacity_message;

	public function __construct( array $args ) {
		$args = wp_parse_args( $args, array(
			'form_id'          => false,
			'service_ids'      => array(),
			'daily_limit'      => 10,
			'capacity_message' => __( 'We are fully booked for that day. Please choose another date.', 'gp-bookings' ),
		));

        $this->form_id          = $args['form_id'];
        $this->service_ids      = array_map( 'intval', (array) $args['service_ids'] );
        $this->daily_limit      = (int) $args['daily_limit'];
        $this->capacity_message = $args['capacity_message'];

        if ( empty( $this->service_ids ) ) {
            return;
        }

        add_action( 'gpb_before_booking_created', array( $this, 'guard_booking_creation' ), 10, 2 );
        add_filter( 'gform_validation', array( $this, 'validate_submission' ) );
    }

    public function guard_booking_creation( array $booking_data, $bookable ) {
        if ( ! $bookable instanceof \GP_Bookings\Service || ! in_array( $bookable->get_id(), $this->service_ids, true ) ) {
            return;
        }

		// Normalize to the start date in the site timezone so nightly/range bookings count correctly.
        $date = $this->normalize_booking_date(
            $booking_data['start_datetime'] ?? '',
            $booking_data['end_datetime'] ?? ( $booking_data['start_datetime'] ?? '' ),
            $bookable
        );
        if ( ! $date ) {
            return;
        }
        $quantity = isset( $booking_data['quantity'] ) ? max( 1, (int) $booking_data['quantity'] ) : 1;

		// Guard again at save time so last-second bookings can't slip past the form validation.
        if ( $this->get_total_for_date( $date ) + $quantity > $this->daily_limit ) {
            throw new \GP_Bookings\Exceptions\CapacityException( $this->capacity_message );
        }
    }

    public function validate_submission( $result ) {
        $is_object = is_object( $result );
        $form      = $is_object ? $result->form : $result['form'];

        if ( $this->form_id && (int) $form['id'] !== (int) $this->form_id ) {
            return $result;
        }

        $is_valid = $is_object ? $result->is_valid : $result['is_valid'];

		// Track per-day totals so multiple booking fields in one submission don't exceed the cap.
        $daily_totals = [];

        foreach ( $form['fields'] as &$field ) {
            if ( ! isset( $field->inputType ) || $field->inputType !== 'gpb_booking' ) {
                continue;
            }

            $children = $field->get_child_fields( $form );
            $service  = $children['service'] ?? null;
            $time     = $children['booking_time'] ?? null;

            if ( ! $service || ! $time ) {
                continue;
            }

            $service_id = isset( $service->gpbService ) ? (int) $service->gpbService : 0;
            if ( ! $service_id || ! in_array( $service_id, $this->service_ids, true ) ) {
                continue;
            }

            $service_model = \GP_Bookings\Service::get( $service_id );
            if ( ! $service_model ) {
                continue;
            }

            $datetime = $this->get_posted_value( (int) $time->id );
            if ( ! $datetime ) {
                continue;
            }

            $date = $this->normalize_booking_date( $datetime, $datetime, $service_model );
            if ( ! $date ) {
                continue;
            }

            $quantity = rgpost( 'input_' . (int) $field->id . '_3' );
            $quantity = $quantity === null || $quantity === '' ? 1 : max( 1, (int) $quantity );

			// Reuse the current total for this date so we only hit the database once per day per submission.
            $current_total = $daily_totals[ $date ] ?? $this->get_total_for_date( $date );

            if ( $current_total + $quantity > $this->daily_limit ) {
                $this->flag_field_error( $form, (int) $time->id );
                $is_valid = false;
                continue;
            }

            $daily_totals[ $date ] = $current_total + $quantity;
        }

        unset( $field );

        if ( ! $is_valid ) {
            $form['validation_message'] = $this->capacity_message;
        }

        if ( $is_object ) {
            $result->form     = $form;
            $result->is_valid = $is_valid;
            return $result;
        }

        $result['form']     = $form;
        $result['is_valid'] = $is_valid;
        return $result;
    }

    private function get_total_for_date( string $date ): int {
        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';
		// Count both pending and confirmed bookings to reflect in-progress reservations.
        $bookings = \GP_Bookings\Queries\Booking_Query::get_bookings_in_range(
            $start,
            $end,
            array(
                'object_id'                     => $this->service_ids,
                'object_type'                   => 'service',
                'status'                        => array( 'pending', 'confirmed' ),
                'exclude_service_with_resource' => false,
            )
        );

        $total = 0;

        foreach ( $bookings as $booking ) {
            $total += (int) $booking->get_quantity();
        }

        return $total;
    }

    private function normalize_booking_date( $start, $end, $bookable ): ?string {
        try {
            $normalized = \GP_Bookings\Booking::normalize_datetime_values( $start, $end, $bookable );
        } catch ( \Throwable $e ) {
            return null;
        }

        return $normalized['start']->format( 'Y-m-d' );
    }

    private function get_posted_value( int $field_id ) {
        $value = rgpost( 'input_' . $field_id );

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        return $value === null || $value === '' ? null : $value;
    }

    private function flag_field_error( array &$form, int $field_id ): void {
        foreach ( $form['fields'] as &$field ) {
            if ( (int) $field->id === $field_id ) {
                $field->failed_validation  = true;
                $field->validation_message = $this->capacity_message;
                break;
            }
        }

        unset( $field );
    }

}

# Configuration
new GPB_Daily_Service_Limit( array(
	'form_id' => 123,
    'service_ids' => array( 45, 67 ),
    'daily_limit' => 10,
    // 'capacity_message' => '',
) );
