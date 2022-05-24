<?php
/**
 * Gravity Wiz // Gravity Forms Help Scout // Catch Help Scout Errors via Email
 * https://gravitywiz.com/
 *
 * Use this snippet to send an email directly to your support queue (or administrator) when there is
 * any Help Scout error. By default, this will generate a link to the entry from which the error was
 * generated and also a link to the Help Scout settings.
 *
 * We use this at Gravity Wiz to catch issues where a ticket was not generated after a support form
 * submission. We send a message to our Help Scout support email so the team is aware of the issue
 * and can correct it immediately.
 */
add_action( 'gform_helpscout_error', function( $feed, $entry, $form, $error_message ) {

		$message   = array();
		$message[] = $error_message;
		$message[] = 'View Entry: ' . GFCommon::replace_variables( '{entry_url}', $form, $entry );
		$message[] = 'Help Scout Settings: ' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=gf_settings&subview=gravityformshelpscout';

		wp_mail( 'support@yoursite.com', 'Help Scout Error', implode( "\n\n", $message ) );
}, 10, 4 );
