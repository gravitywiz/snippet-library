<?php
/**
 * Gravity Perks // Advanced Save & Continue // Customize Resume Message
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 *
 * Customize the message that appears at the top of the form when resuming a draft.
 */
add_filter( 'gpasc_resume_notice_message', function( $message, $form, $display_name, $draft_data ) {
	$message = sprintf( 'Resuming application from %s', $display_name );
	return $message;
}, 10, 4 );
