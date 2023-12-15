<?php
/**
 * Gravity Perks // Notification Scheduler // Trigger Function on Send
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 *
 * This snippet demonstrates how to trigger a custom function using a scheduled notification. The notification
 * is suppressed by default but can also be triggered by removing the specified line below. The trigger
 * notification is identified by its subject line.
 */
add_filter( 'gform_pre_send_email', function( $email, $message_format, $notification, $entry ) {

	// Only intercept emails sent via Notification Scheduler.
	if ( ! doing_action( 'gpns_cron' ) ) {
		return $email;
	}

	// Update "Trigger: My Custom Function" to your trigger notification's subject line.
	if ( $notification['subject'] !== 'Trigger: My Custom Function' ) {
		return $email;
	}

	// Delete this line if you want the notification to still be sent.
	$email['abort_email'] = true;

	// Update this to your own custom function name.
	my_custom_function();

	return $email;
}, 10, 4 );
