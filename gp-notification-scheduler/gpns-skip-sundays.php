<?php
/**
 * Gravity Perks // Notification Scheduler // Skip Sundays for Drip Campaigns
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 *
 * This snippet will automatically adjust a notification's "Delay" schedule to ensure that it is not sent on a Sunday.
 * All subsequent notifications with have their delay automatically adjusted to ensure that they arrive in the correct
 * order. This requires that all notifications be created in the exact order that they should be received.
 */
add_filter( 'gpns_schedule_timestamp_123', function( $schedule_datetime ) {
	static $buffer;

	if ( $buffer === null ) {
		$buffer = 0;
	}

	$date = DateTime::createFromFormat( 'U', $schedule_datetime );

	if ( $buffer > 0 ) {
		$date->add( new DateInterval( "P{$buffer}D" ) );
	}

	if ( $date->format( 'l' ) === 'Sunday' ) {
		$date->add( new DateInterval( 'P1D' ) );
		$buffer++;
	}

	if ( $buffer <= 0 ) {
		return $schedule_datetime;
	}

	return $date->format( 'U' );
}, 10, 5 );
