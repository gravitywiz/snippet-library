<?php
/**
 * Gravity Perks // Notification Scheduler // Trigger Twilio Feed
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 *
 * Use a scheduled notification to trigger a Twilio feed instead.
 *
 * Instruction Video: https://www.loom.com/share/58faeab7b4884ccbb177fa3e30c9587e
 *
 * Instructions:
 *
 *  1. Setup your Twilio feed.
 *      a. In the "Name" setting of your Twilio feed include the following string "twilio.FID@notificationscheduler.com".
 *      b. Replace "FID" with your Twilio feed's ID. You can find this in the URL via the "fid" parameter.
 *  2. Setup your scheduled notification.
 *      a. Set the "Send To Email" to the same string you included in your Twilio feed's name (e.g.
 *          "twilio.FID@notificationscheduler.com" with the FID replaced with your actual FID).
 *      b. Configure your desired schedule with Notification Scheduler's "Schedule" setting.
 *      c. The notification has other required fields. Fill them out however you wish. They will not be used.
 *
 * When the scheduled notification is sent, the actual email will be aborted and the Twilio feed will be sent instead.
 *
 * Plugin Name:  GP Notification Scheduler — Twilio
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 * Description:  Trigger a Twilio feed via Notification Scheduler.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_gravityformstwilio_pre_process_feeds', function( $feeds ) {

	$filtered = array();

	foreach ( $feeds as $feed ) {
		if ( ! preg_match( '/twilio(?:.([0-9]+))@notificationscheduler.com/', $feed['meta']['feedName'], $match ) ) {
			$filtered[] = $feed;
		}
	}

	return $filtered;
} );

add_filter( 'gform_pre_send_email', function( $email, $message_format, $notification, $entry ) {

	// Only intercept emails sent via Notification Scheduler.
	if ( ! doing_action( 'gpns_cron' ) ) {
		return $email;
	}

	if ( ! preg_match( '/twilio(?:.([0-9]+))@notificationscheduler.com/', $notification['toEmail'], $match ) ) {
		return $email;
	}

	$email['abort_email'] = true;

	$twilio_feed = gf_twilio()->get_feed( (int) $match[1] );
	if ( $twilio_feed ) {
		gf_twilio()->process_feed( $twilio_feed, $entry, GFAPI::get_form( $entry['form_id'] ) );
	}

	return $email;
}, 10, 4 );
