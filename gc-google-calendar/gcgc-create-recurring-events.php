<?php

/**
 * Gravity Connect // Google Calendar // Create Recurring Event
 *
 * Make events created by GC Google Calendar recurring.
 *
 * @reference The Google Calendar insert event API reference: https://developers.google.com/workspace/calendar/api/v3/reference/events/insert
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2.
 */

function gcgc_create_recurring_event( $form_id, $feed_id, $rules ) {
	function add_recurrence( $event_params, $rules ) {
		if ( ! is_array( rgar( $event_params, 'recurrence' ) ) ) {
			$event_params['recurrence'] = array();
		}

		$event_params['recurrence'] = array_merge(
			$event_params['recurrence'],
			$rules,
		);

		return $event_params;
	}

	add_filter(
		implode( '_', array( 'gcgc_create_calendar_event_params', $form_id, $feed_id ) ),
		function(
				$event_params,
				$form,
				$feed,
				$entry
		) use ( $rules ) {
			return add_recurrence( $event_params, $rules );
		},
		10,
		4
	);

	add_filter(
		implode( '_', array( 'gcgc_update_calendar_event_params', $form_id, $feed_id ) ),
		function(
				$event_params,
				$form,
				$feed,
				$entry,
				$event_id
		) use ( $rules ) {
			return add_recurrence( $event_params, $rules );
		},
		10,
		5
	);
}

// Usage Examples:

// Create a weekly event that recurs exactly three times.
// Scoped to form id 12 / feed id 29
gcgc_create_recurring_event(
	'12',
	'29',
	array( 'RRULE:FREQ=WEEKLY;COUNT=3' ),
);

