<?php
/**
 * Gravity Perks // Notification Scheduler // Frontend Unsubscribe Form
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 *
 * Create a form that can manage unsubscribing (or resubscribing) an email address from Notification Scheduler.
 */
// Update "123" to your form ID.
add_action( 'gform_after_submission_123', function( $entry ) {
	global $wpdb;

	// Update "4" to the ID of the field in which the email to be unsubscribed or resubscribed is entered.
	$email  = rgar( $entry, '4' );

	// Update "5" to the ID of the field in which the user will indicate if the field should be unsubscribed or resubscribed.
	$action = rgar( $entry, '5' );

	if ( ! $email || ! $action ) {
		return;
	}

	if ( $action === 'unsubscribe' ) {
		$wpdb->insert( $wpdb->gpns_unsubscribes, array(
			'email'         => $email,
			'scope'         => 'all',
			'timestamp_gmt' => current_time( 'mysql', true ),
		) );
	} elseif ( $action === 'resubscribe' ) {
		$wpdb->delete( $wpdb->gpns_unsubscribes, array(
			'email' => $email,
		) );
	}

} );
