<?php
/**
 * Gravity Perks // Bookings // Filter Google Calendar Events Blocking Availability
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * By default, every busy event on your connected Google Calendar blocks booking availability,
 * including invitations you haven't accepted and events added by shared calendars. This snippet
 * lets you choose which events block availability and which are ignored, leaving those times
 * open for booking.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Pick or adjust an example in the configuration area at the bottom of the snippet.
 *    Options can be combined:
 *
 *    - `only_block_own_events` (true/false) — Only events you created or organize
 *      block availability. Invitations and events added by shared calendars are ignored.
 *    - `ignore_unaccepted_invitations` (true/false) — Ignore invitations you haven't
 *      accepted (tentative, unanswered, declined).
 *    - `ignore_if_title_contains` (array of phrases) — Ignore any event whose title
 *      contains one of these (case-insensitive).
 *    - `only_block_if_title_contains` (array of phrases) — ONLY events whose title
 *      contains one of these block availability (case-insensitive).
 *
 * 3. Re-sync your Google Calendar connection to ensure the updated settings take effect.
 */
class GPB_Google_Calendar_Event_Filter {

	private $config;

	public function __construct( array $config = array() ) {
		$this->config = array_merge(
			array(
				'only_block_own_events'         => false,
				'ignore_unaccepted_invitations' => false,
				'ignore_if_title_contains'      => array(),
				'only_block_if_title_contains'  => array(),
			),
			$config
		);

		add_filter( 'gpb_google_calendar_event_block_data', array( $this, 'filter_block_data' ), 10, 2 );
	}

	public function filter_block_data( $block_data, $event ) {
		if ( ! is_array( $block_data ) ) {
			return $block_data;
		}

		// Organizer/creator "self" is only set for events the connected account owns.
		if ( $this->config['only_block_own_events'] && empty( $event['organizer']['self'] ) && empty( $event['creator']['self'] ) ) {
			return null;
		}

		if ( $this->config['ignore_unaccepted_invitations'] && $this->is_unaccepted_invitation( $event ) ) {
			return null;
		}

		$title = (string) ( $event['summary'] ?? '' );

		if ( $this->title_matches( $title, $this->config['ignore_if_title_contains'] ) ) {
			return null;
		}

		if ( $this->config['only_block_if_title_contains'] && ! $this->title_matches( $title, $this->config['only_block_if_title_contains'] ) ) {
			return null;
		}

		return $block_data;
	}

	private function is_unaccepted_invitation( $event ) {
		foreach ( (array) ( $event['attendees'] ?? array() ) as $attendee ) {
			if ( ! empty( $attendee['self'] ) ) {
				return ( $attendee['responseStatus'] ?? '' ) !== 'accepted';
			}
		}

		return false;
	}

	private function title_matches( $title, $needles ) {
		$title = strtolower( $title );

		foreach ( (array) $needles as $needle ) {
			$needle = strtolower( trim( (string) $needle ) );

			if ( $needle !== '' && strpos( $title, $needle ) !== false ) {
				return true;
			}
		}

		return false;
	}
}

# Configuration

// Example: only events you created or organize block availability.
new GPB_Google_Calendar_Event_Filter(
	array(
		'only_block_own_events' => true,
	)
);

// Example: everything blocks availability except invitations you haven't accepted.
// new GPB_Google_Calendar_Event_Filter(
// 	array(
// 		'ignore_unaccepted_invitations' => true,
// 	)
// );

// Example: events with "Hold" or "Tentative" in the title don't block availability.
// new GPB_Google_Calendar_Event_Filter(
// 	array(
// 		'ignore_if_title_contains' => array( 'Hold', 'Tentative' ),
// 	)
// );

// Example: ONLY events with "Busy" or "PTO" in the title block availability.
// new GPB_Google_Calendar_Event_Filter(
// 	array(
// 		'only_block_if_title_contains' => array( 'Busy', 'PTO' ),
// 	)
// );

// Example: combine options — ignore unaccepted invitations and any event titled "Hold".
// new GPB_Google_Calendar_Event_Filter(
// 	array(
// 		'ignore_unaccepted_invitations' => true,
// 		'ignore_if_title_contains'      => array( 'Hold' ),
// 	)
// );
