<?php
/**
 * Gravity Perks // Notification Scheduler // Send Notification on the Last Day of Every Month
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 */
add_filter( 'gpns_schedule_timestamp', function ( $timestamp, $notification, $entry, $is_recurring, $current_time ) {
	// Update "123" to the Form Id/
	$form_id = 123;
	// Update "61f43ccb47dfd" to the Notification ID.
	$notification_id = '61f43ccb47dfd';

	// See https://www.php.net/manual/en/datetime.formats.php for supported date/time formats.
	if ( $is_recurring ) { // If the notification has just been triggered to send and the next recurring notification is being scheduled (if repeat is enabled)
		$desired_time = 'last day of next month 12:00 PM';
	} else {
		$desired_time = 'last day of this month 12:00 PM';
	}

	if ( (int) $entry['form_id'] !== (int) $form_id || $notification['id'] !== $notification_id ) {
		return $timestamp;
	}

	$local_timestamp = date( 'Y-m-d H:i:s', strtotime( $desired_time ) );
	$utc_timestamp   = strtotime( get_gmt_from_date( $local_timestamp ) );

	return $utc_timestamp;
}, 10, 5 );
