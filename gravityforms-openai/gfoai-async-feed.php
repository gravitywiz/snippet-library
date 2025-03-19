<?php
/**
 * Gravity Wiz // Gravity Forms OpenAI // Enable asynchronous feed processing and notifications.
 *
 * API requests to OpenAI can be quite slow given the amount of processing that is happening behind the scenes. To
 * speed up form submission, we can make the feed asynchronous as well as the notifications.
 *
 * Instructions:
 *  1. Install per https://gravitywiz.com/how-do-i-install-a-snippet/
 *  2. Update FORMID accordingly.
 */
add_filter( 'gform_is_asynchronous_notifications_enabled_FORMID', '__return_true' );
add_filter( 'gform_is_feed_asynchronous_FORMID', function( $is_async, $feed ) {
	// Only make feeds async if they have the slug of gravityforms-openai.
	if ( $feed['addon_slug'] === 'gravityforms-openai' ) {
		return true;
	}

	return $is_async;
}, 10, 2 );
