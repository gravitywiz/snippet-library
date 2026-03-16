<?php
/**
 * Gravity Perks // Bookings // Custom Calendar Event Title Format
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Build admin calendar event titles from booking detail tokens and entry field values.
 * Create multiple instances to apply different formats per service and/or resource.
 *
 * Available booking tokens:
 * - `{title}`        Default event title (e.g. "Resource — Service").
 * - `{customerName}` Customer name.
 * - `{serviceName}`  Service name.
 * - `{resourceName}` Resource name.
 * - `{bookingId}`    Booking ID.
 * - `{entryId}`      Entry ID.
 * - `{status}`       Booking status.
 * - `{start}`        Start date/time.
 * - `{end}`          End date/time.
 *
 * Gravity Forms merge tags can also be used to include entry field values and entry
 * meta in the title (e.g. `{Phone:5}`, `{Name (Last):3.6}`, `{payment_status}`).
 */
class GPB_Custom_Event_Title {

	private $title_format;
	private $service_id;
	private $resource_id;

	public function __construct( $args ) {
		$this->title_format = $args['title_format'] ?? '{customerName} - {serviceName}';
		$this->service_id   = isset( $args['service_id'] ) ? (int) $args['service_id'] : null;
		$this->resource_id  = isset( $args['resource_id'] ) ? (int) $args['resource_id'] : null;

		add_filter( 'gpb_admin_calendar_event', array( $this, 'filter_event' ), 10, 2 );
	}

	public function filter_event( $event, $booking ) {
		if ( ! $this->should_apply( $event ) ) {
			return $event;
		}

		$tokens = $this->get_tokens( $event, $booking );
		$title  = preg_replace_callback( '/\{(\w+)\}/', function ( $m ) use ( $tokens ) {
			return $tokens[ $m[1] ] ?? $m[0];
		}, $this->title_format );

		// Process any remaining Gravity Forms merge tags.
		$entry = $booking->get_entry();
		if ( $entry ) {
			$form  = GFAPI::get_form( $entry['form_id'] );
			$title = GFCommon::replace_variables( $title, $form, $entry, false, false, false, 'text' );
		}

		// Collapse whitespace and trim leftover separators from empty tokens.
		$title = trim( preg_replace( '/\s+/', ' ', $title ) );
		$title = preg_replace( '/^[\s\-—\/]+|[\s\-—\/]+$/u', '', $title );

		$event['title'] = $title !== '' ? $title : ( $event['title'] ?? '' );

		return $event;
	}

	private function should_apply( $event ) {
		if ( $this->service_id && (int) ( $event['serviceId'] ?? 0 ) !== $this->service_id ) {
			return false;
		}
		if ( $this->resource_id && (int) ( $event['resourceId'] ?? 0 ) !== $this->resource_id ) {
			return false;
		}
		return true;
	}

	private function get_tokens( $event, $booking ) {
		$tokens = array();
		foreach ( $event as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$tokens[ $key ] = (string) $value;
			}
		}

		// Alias 'id' as 'bookingId' for readability in title formats.
		$tokens['bookingId'] = $tokens['id'] ?? '';

		$bookable = $booking->get_bookable();
		if ( ( $event['objectType'] ?? '' ) === 'resource' ) {
			$tokens['resourceName'] = $bookable ? $bookable->get_name() : '';
			$parent_id              = $booking->get_parent_booking_id();
			$parent_booking         = $parent_id ? \GP_Bookings\Booking::get( $parent_id ) : null;
			$tokens['serviceName']  = $parent_booking && $parent_booking->get_bookable() ? $parent_booking->get_bookable()->get_name() : '';
		} else {
			$tokens['serviceName']  = $bookable ? $bookable->get_name() : '';
			$tokens['resourceName'] = '';
		}

		return $tokens;
	}
}

# Configuration

new GPB_Custom_Event_Title( array(
	'title_format' => '{customerName} - {serviceName}',
	// 'service_id'  => 12, // Optionally restrict to a specific service.
	// 'resource_id' => 7,  // Optionally restrict to a specific resource.
) );
