<?php
/**
 * Gravity Perks // Blocklist // Enable User-Agent Checking
 * https://gravitywiz.com/documentation/gravity-forms-blocklist/
 */
// Update "123" to the Form ID.
add_filter( 'gpcb_validate_user_agent_123', '__return_true' );
