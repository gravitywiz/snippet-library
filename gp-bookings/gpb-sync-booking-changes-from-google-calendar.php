<?php
/**
 * Gravity Perks // Bookings // Sync Booking Changes from Google Calendar
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Experimental Snippet 🧪
 *
 * Applies changes made to a booking's event in Google Calendar back to the booking on your
 * site. When an event is moved or resized, the booking is rescheduled to match, which also
 * moves its resources, updates the entry, and frees the old time slot while blocking the
 * new one.
 *
 * Changes are applied on the next calendar sync (hourly by default), or immediately via the
 * "Sync Now" button on the connection in your service or resource settings.
 *
 * Requires GC Google Calendar and a connection whose sync mode sends bookings.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
class GPB_Sync_Booking_Changes_From_Google_Calendar {

	private $respect_availability;

	public function __construct( array $args = array() ) {
		$args = wp_parse_args( $args, array(
			'respect_availability' => false,
		) );

		$this->respect_availability = (bool) $args['respect_availability'];

		add_action( 'gpb_sync_calendar', array( $this, 'apply_calendar_changes' ), 20, 1 );
		add_filter( 'rest_request_after_callbacks', array( $this, 'apply_after_manual_sync' ), 10, 3 );
	}

	public function apply_after_manual_sync( $response, $handler, $request ) {
		$route = $request->get_route();

		if ( strpos( $route, '/calendar-connect/' ) !== false && substr( $route, -5 ) === '/sync'
			&& ! is_wp_error( $response ) && $request->get_param( 'config_id' ) ) {
			$this->apply_calendar_changes( (int) $request->get_param( 'config_id' ) );
		}

		return $response;
	}

	public function apply_calendar_changes( $config_id ) {
		if ( ! function_exists( 'gc_google_calendar' ) || ! class_exists( '\GP_Bookings\Calendar\ICS_Calendar_Config' ) ) {
			return;
		}

		$config = \GP_Bookings\Calendar\ICS_Calendar_Config::get( (int) $config_id );

		if ( ! $config || ! $config->should_send_bookings() ) {
			return;
		}

		foreach ( $this->get_events( $config ) as $event ) {
			$this->apply_event( $event );
		}
	}

	private function get_events( $config ) {
		$start = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$end   = ( clone $start )->modify( '+6 months' );

		try {
			$events = gc_google_calendar()->api()->get_events(
				$config->get_google_account_id(),
				$config->get_google_calendar_id(),
				array(
					'timeMin'      => $start->format( DateTime::ATOM ),
					'timeMax'      => $end->format( DateTime::ATOM ),
					'singleEvents' => true,
					'orderBy'      => 'startTime',
					'eventTypes'   => array( 'default' ),
				)
			);
		} catch ( \Throwable $e ) {
			$this->log( 'could not fetch events: ' . $e->getMessage() );
			return array();
		}

		return (array) $events;
	}

	private function apply_event( $event ) {
		$props = $event['extendedProperties']['private'] ?? array();

		if ( empty( $props['gpb_booking_id'] ) || ! $this->is_same_site( $props['gpb_site_url'] ?? '' )
			|| empty( $event['start']['dateTime'] ) || empty( $event['end']['dateTime'] ) ) {
			return;
		}

		$booking = \GP_Bookings\Booking::get( (int) $props['gpb_booking_id'] );

		if ( ! $booking || $booking->get_status() === 'cancelled' ) {
			return;
		}

		$start = $this->to_local( $event['start']['dateTime'] );
		$end   = $this->to_local( $event['end']['dateTime'] );

		if ( $start === $booking->get_start_datetime() && $end === $booking->get_end_datetime() ) {
			return;
		}

		try {
			$booking->update_booking(
				array(
					'start_datetime' => $start,
					'end_datetime'   => $end,
				),
				'Updated from Google Calendar.',
				! $this->respect_availability
			);
		} catch ( \Throwable $e ) {
			$this->log( sprintf( 'booking %d not updated: %s', $booking->get_id(), $e->getMessage() ) );
		}
	}

	private function to_local( $iso ) {
		return ( new DateTime( $iso ) )->setTimezone( wp_timezone() )->format( 'Y-m-d H:i:s' );
	}

	private function is_same_site( $url ) {
		$normalize = static function( $value ) {
			return strtolower( untrailingslashit( preg_replace( '#^https?://#', '', (string) $value ) ) );
		};

		return $normalize( $url ) === $normalize( home_url() );
	}

	private function log( $message ) {
		gp_bookings()->log_error( 'GPB_Sync_Booking_Changes_From_Google_Calendar: ' . $message );
	}

}

# Configuration

new GPB_Sync_Booking_Changes_From_Google_Calendar();

// Example: refuse changes that fall outside your availability or over capacity.
// new GPB_Sync_Booking_Changes_From_Google_Calendar( array(
// 	'respect_availability' => true,
// ) );
