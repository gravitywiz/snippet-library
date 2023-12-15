<?php
/**
 * Gravity Perks // Easy Passthrough // Disable Passing Through Entries Without a Token
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 */
add_filter( 'gpep_bypass_session_init', function() {
	if ( ! rgget( 'ep_token' ) || ! gp_easy_passthrough()->get_entry_for_token( rgget( 'ep_token' ) ) ) {
		$session = gp_easy_passthrough()->session_manager();
		$session->reset();
	}
} );
