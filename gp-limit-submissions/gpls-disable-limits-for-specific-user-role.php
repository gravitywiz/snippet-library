<?php
/**
 * Gravity Perks // GP Limit Submissions // Disable Limits for a Specific User Role
 * http://gravitywiz.com/documentaiton/gravity-forms-limit-submissions/
 */
// Update '123' to the ID of the Form.
add_filter( 'gpls_rule_groups_123', function( $rule_groups, $form_id ) {
	// Update to the slug of the role, i.e. administrator, editor, author, contributor, subscriber.
	$role_to_check = 'administrator';
	$user          = wp_get_current_user();
	if ( in_array( $role_to_check, (array) $user->roles ) ) {
		$rule_groups = array();
	}
	return $rule_groups;
}, 10, 2 );
