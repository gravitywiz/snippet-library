<?php
/**
 * Gravity Perks // Notification Scheduler // Convert Field-based Schedules to UTC
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 */
add_filter( 'gpns_schedule_timestamp', function( $timestamp, $notification, $entry, $is_recurring, $current_time ) {

	if ( $notification['scheduleType'] === 'field' ) {

		$date = new DateTime();
		$date->setTimestamp( $timestamp );
		$date->setTimezone( wp_timezone() );

		$timestamp += $date->getOffset();

	}

	return $timestamp;
}, 10, 5 );
