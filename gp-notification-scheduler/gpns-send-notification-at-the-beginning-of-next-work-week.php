<?php
/**
 * Gravity Perks // Notification Scheduler // Send Notification at the Beginning of the Next Work Week
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 */
add_filter( 'gpns_schedule_timestamp', function ( $timestamp, $notification, $entry, $is_recurring, $current_time ) {

	// See https://www.php.net/manual/en/datetime.formats.php for supported date/time formats.
	$desired_time = 'next monday 12:00 PM';

	// Update "123" to the form ID.
	$form_id = 123;

	// Update "1a23bc456def7" to the notificaiton ID.
	$notification_id = '1a23bc456def7';

	if ( (int) $entry['form_id'] !== (int) $form_id || $notification['id'] !== $notification_id ) {
		return $timestamp;
	}

	$local_timestamp = gmdate( 'Y-m-d H:i:s', strtotime( $desired_time ) );
	$utc_timestamp   = strtotime( get_gmt_from_date( $local_timestamp ) );

	return $utc_timestamp;
}, 10, 5 );
