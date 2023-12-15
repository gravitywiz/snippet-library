<?php
/**
 * Gravity Perks // GP Limit Submissions // Disable Limits for a Specific IPs
 * http://gravitywiz.com/documentaiton/gravity-forms-limit-submissions/
 */
// Update "123" to the ID of the form.
add_filter( 'gpls_rule_groups_123', function( $rule_groups, $form_id ) {
	// Update to a list of IP addresses that should not be limited.
	$excempt_ips = array( '192.0.2.255', '198.51.100.10', '203.0.113.11' );
	if ( in_array( GFFormsModel::get_ip(), $excempt_ips ) ) {
		$rule_groups = array();
	}
	return $rule_groups;
}, 10, 2 );
