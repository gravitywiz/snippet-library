<?php
/**
 * Gravity Perks // Notification Scheduler // Change Scope and Unsubscribe Success URL
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 */
// Update "123" to the form ID and "1a23bc456def7" to the notification ID.
add_filter( 'gpns_unsubscribe_url_args_123_1a23bc456def7', function ( $unsubscribe_info ) {
	// Only unsubscribe from current notification
	$unsubscribe_info['scope'] = 'nid';

	// Change unsubscribe success URL.
	$unsubscribe_info['url'] = home_url( '/example-unsubscribe-page' );

	return $unsubscribe_info;
} );
