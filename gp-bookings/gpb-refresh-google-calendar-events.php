<?php
/**
 * Gravity Perks // Bookings // Refresh Google Calendar Events
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Experimental Snippet 🧪
 *
 * Rewrites the events GP Bookings has already pushed to Google Calendar so their titles and
 * descriptions match the connection's current Event Title and Event Description.
 *
 * A calendar sync only pushes bookings that do not have an event yet, so changing those
 * templates applies to new bookings only and leaves every existing event on the old text. This
 * snippet re-pushes the bookings that are already mapped to the calendar, rebuilding each event
 * from the current templates.
 *
 * Events are refreshed when you click "Sync Now" on the Google Calendar connection in your
 * service or resource settings. By default, only bookings that have not yet ended are
 * refreshed. See the configuration options at the bottom of the snippet to include past
 * bookings or also refresh during scheduled syncs.
 *
 * Requires GC Google Calendar and a connection whose sync mode sends bookings.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the Event Title and/or Event Description on your Google Calendar connection.
 *
 * 3. Click "Sync Now" on that same connection to bring its existing events up to date.
 */
class GPB_Refresh_Google_Calendar_Events {

	private $include_past;
	private $refresh_on_scheduled_sync;

	public function __construct( array $args = array() ) {
		$args = wp_parse_args( $args, array(
			'include_past'              => false,
			'refresh_on_scheduled_sync' => false,
		) );

		$this->include_past              = (bool) $args['include_past'];
		$this->refresh_on_scheduled_sync = (bool) $args['refresh_on_scheduled_sync'];

		add_filter( 'rest_request_after_callbacks', array( $this, 'refresh_after_manual_sync' ), 20, 3 );

		if ( $this->refresh_on_scheduled_sync ) {
			add_action( 'gpb_sync_calendar', array( $this, 'refresh_events' ), 30, 1 );
		}
	}


	public function refresh_after_manual_sync( $response, $handler, $request ) {
		$route = $request->get_route();

		if ( strpos( $route, '/calendar-connect/' ) !== false && substr( $route, -5 ) === '/sync'
			&& ! is_wp_error( $response ) && $request->get_param( 'config_id' ) ) {
			$this->refresh_events( (int) $request->get_param( 'config_id' ) );
		}

		return $response;
	}

	public function refresh_events( $config_id ) {
		if ( ! function_exists( 'gc_google_calendar' ) || ! class_exists( '\GP_Bookings\Calendar\ICS_Calendar_Config' ) ) {
			return;
		}

		$config = \GP_Bookings\Calendar\ICS_Calendar_Config::get( (int) $config_id );

		if ( ! $config || ! $config->is_google() || ! $config->should_send_bookings() ) {
			return;
		}

		$pusher    = new \GP_Bookings\Calendar\Google_Booking_Pusher();
		$now       = current_time( 'mysql' );
		$refreshed = array();

		foreach ( $this->get_mapped_booking_ids( $config ) as $booking_id ) {
			if ( isset( $refreshed[ $booking_id ] ) ) {
				continue;
			}

			$refreshed[ $booking_id ] = true;

			$booking = \GP_Bookings\Booking::get( $booking_id );

			if ( ! $booking || $booking->get_status() === 'cancelled' ) {
				continue;
			}

			if ( ! $this->include_past && $booking->get_end_datetime() < $now ) {
				continue;
			}

			$pusher->push_booking_updated( $booking, array(), array() );
		}
	}

	private function get_mapped_booking_ids( $config ) {
		global $wpdb;

		$table_name = \GP_Bookings\Database::table_google_event_map();

		$booking_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT booking_id FROM {$table_name} WHERE config_id = %d",
			$config->get_id()
		) );

		return array_map( 'intval', (array) $booking_ids );
	}

}

# Configuration

new GPB_Refresh_Google_Calendar_Events( array(
	'include_past'              => false, // Also refresh bookings that have already ended.
	'refresh_on_scheduled_sync' => false, // Also refresh events during the scheduled hourly sync.
) );
