<?php
/**
 * Gravity Perks // Limit Submissions // Enable `WPML String Translate` for Custom Limit Message
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */
add_action( 'init', function() {
	if ( function_exists( 'icl_register_string' ) && function_exists( 'gp_limit_submissions' ) ) {
		$feeds = GFAPI::get_feeds( null, null, 'gp-limit-submissions', true );
		foreach ( $feeds as $feed ) {
			icl_register_string( 'gp-limit-submissions', "rule-limit-message-{$feed['id']}", $feed['meta']['rule_limit_message'] );
		}
	}
} );
