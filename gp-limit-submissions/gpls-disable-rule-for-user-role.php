<?php
/**
 * Gravity Perks // GP Limit Submissions // Disable Rule for a Specific User Role
 * http://gravitywiz.com/documentaiton/gravity-forms-limit-submissions/
 */
// Change '123' to the ID of the Form
add_filter( 'gpls_rule_groups_123', function( $rule_groups, $form_id ) {
	$user = wp_get_current_user();
	//Change administrator to the role that will use the form without limit
	if ( in_array( 'administrator', (array) $user->roles ) ) {
		$rule_groups = array();
	}
	return $rule_groups;
}, 10, 2 );