<?php
/**
 * Gravity Perks // Notification Scheduler // Change Scope and Unsubscribe Success URL
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 */
// Update FORMID to the Form ID and NID to the Notification ID.
add_filter( 'gpns_unsubscribe_url_args_FORMID_NID', function ( $unsubscribe_info ) {
	// Only unsubscribe from current notification
	$unsubscribe_info['scope'] = 'nid';

	// Change unsubscribe success URL.
	$unsubscribe_info['url'] = home_url( '/example-unsubscribe-page' );

	return $unsubscribe_info;
} );
