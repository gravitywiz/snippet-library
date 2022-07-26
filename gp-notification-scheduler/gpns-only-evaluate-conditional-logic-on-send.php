<?php
/**
 * Gravity Perks // Notification Scheduler // Only Evaluate Conditional Logic on Send
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 *
 * This snippet updates Notification Scheduler evaluate conditional logic when a scheduled notification
 * is about to be sent but to ignore conditional when scheduling notifications. This is useful when creating
 * scheduled notifications that should be sent if a value has been changed since the original submission.
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_submission_filter_123', function( $form ) {
	foreach ( $form['notifications'] as &$notification ) {
		if ( rgar( $notification, 'scheduleType', 'immediate' ) !== 'immediate' ) {
			$notification['conditionalLogic'] = null;
		}
	}
	return $form;
} );

// Update "123" to your form ID.
add_filter( 'gpns_evaluate_conditional_logic_on_send_123', '__return_true' );
