<?php
/**
 * Gravity Wiz // Gravity Forms OpenAI // Process shortcodes on Message Prompts
 *
 * Processing of shortcodes on the message prompts is disabled by default. Use this snippet to enable it
 *
 * Instructions:
 *  1. Install per https://gravitywiz.com/how-do-i-install-a-snippet/
 */
add_filter( 'gform_gravityforms-openai_pre_process_feeds', function ( $feeds, $entry ) {
	foreach ( $feeds as &$feed ) {
		// Process shortcode on chat completion message prompt
		if ( $feed['meta']['chat_completions_message'] ) {
			$feed['meta']['chat_completions_message'] = do_shortcode( $feed['meta']['chat_completions_message'] );
		}
	}

	return $feeds;
}, 10, 2 );
