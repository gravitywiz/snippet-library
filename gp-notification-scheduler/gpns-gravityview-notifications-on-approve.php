<?php
/**
 * Gravity Wiz // Notification Scheduler // Support Gravity View Notification Triggers
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 *
 * Use this snippet when using Gravity View Notification Trigger with Gravity Perks Notification Scheduler.
 */
add_filter( 'gpns_schedule_timestamp', function ( $timestamp, $notification, $entry, $is_recurring, $current_time ) {
	// Only process for GravityView notification events.
	if ( strpos( $notification['event'], 'gravityview') === false ) {
		return $timestamp;
	}

   return time() + 5; // Use current time plus a few seconds to force-schedule GravityView event.
}, 10, 5 );