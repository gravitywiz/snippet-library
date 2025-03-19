<?php
/**
 * Gravity Wiz // Gravity Forms // Remove Empty Emails from Notification Email Lists
 * https://gravitywiz.com/
 *
 * If you're passing merge tags as an email list (e.g. {admin_email},{Email:1},{Alternate Email:3}) and one of those
 * emails is empty, Gravity Forms won't send the email to any of the emails in the list.
 *
 * This snippet intercepts notifications and processes the `to`, `cc` and `bcc` properties, removing any empty emails.
 */
add_filter( 'gform_notification', function( $notification, $form, $entry ) {
	foreach ( array( 'to', 'cc', 'bcc' ) as $key ) {
		if ( ! empty( $notification[ $key ] ) ) {
			$notification[ $key ] = GFCommon::replace_variables( $notification[ $key ], $form, $entry, false, false, false, 'text' );
			$emails               = array_filter( explode( ',', $notification[ $key ] ) );
			$notification[ $key ] = implode( ',', $emails );
		}
	}
	return $notification;
}, 10, 3 );
