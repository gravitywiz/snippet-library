<?php
/**
 * Gravity Wiz // Gravity Forms // Notify Author When Post is Published
 * https://gravitywiz.com/notify-author-when-post-is-published/
 *
 * Send an email to the post author when the post is published.
 *
 * Plugin Name:  Gravity Forms - Notify Author When Post is Published
 * Plugin URI:   https://gravitywiz.com/notify-author-when-post-is-published/
 * Description:  Send an email to the post author when the post is published.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com/
 */
add_action( 'publish_post', 'gw_notify_author_on_publish' );
function gw_notify_author_on_publish( $post_id ) {

	$from_name  = 'Your Name';
	$from_email = 'your@email.com';
	$subject    = 'Your Subject Here';
	$message    = 'Your message here.';

	/* No need to edit beyond this point */

	// if this meta key is not set, this post was not created by a Gravity Form
	if ( ! get_post_meta( $post_id, '_gform-form-id', true ) ) {
		return;
	}

	// make sure we haven't already sent a notification for this post
	if ( get_post_meta( $post_id, '_gform-notified', true ) ) {
		return;
	}

	$post   = get_post( $post_id );
	$author = new WP_User( $post->post_author );
	$email  = $author->get( 'user_email' );

	$headers[] = "From: '{$from_name}' <{$from_email}>";
	$headers[] = 'Content-type: text/html; charset=' . get_option( 'blog_charset' );

	wp_mail( $email, $subject, $message, $headers );

	update_post_meta( $post_id, '_gform-notified', 1 );

}
