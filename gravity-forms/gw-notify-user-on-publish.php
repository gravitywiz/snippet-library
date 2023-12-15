<?php
/**
 * Gravity Wiz // Gravity Forms // Notify User When Submitted Post is Published
 * https://gravitywiz.com/notify-user-when-submitted-post-is-published/
 *
 * Send an email to a user when the post they submitted is published.
 */
add_action( 'publish_post', 'gw_notify_on_publish' );
function gw_notify_on_publish( $post_id ) {

	$custom_field_name = 'your_custom_field_name';
	$from_name         = 'Your Name';
	$from_email        = 'your@email.com';
	$subject           = 'Your Subject Here';
	$message           = 'Your message here.';

	/* No need to edit beyond this point */

	// if this meta key is not set, this post was not created by a Gravity Form
	if ( ! get_post_meta( $post_id, '_gform-form-id', true ) ) {
		return;
	}

	// make sure we haven't already sent a notification for this post
	if ( get_post_meta( $post_id, '_gform-notified', true ) ) {
		return;
	}

	$email = get_post_meta( $post_id, $custom_field_name, true );

	$headers   = array();
	$headers[] = "From: '{$from_name}' <{$from_email}>";
	$headers[] = 'Content-type: text/html; charset=' . get_option( 'blog_charset' );

	wp_mail( $email, $subject, $message, $headers );

	update_post_meta( $post_id, '_gform-notified', 1 );

}
