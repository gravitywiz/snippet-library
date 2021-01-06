<?php
/**
 * Gravity Perks // GP Limit Submissions // Disable Rule for a Specific User Role
 * http://gravitywiz.com/documentaiton/gravity-forms-limit-submissions/
 */
// Change '123' to the ID of the Form.
add_filter( 'gpls_rule_groups_123', function( $rule_groups, $form_id ) {
	$user          = wp_get_current_user();
	$role_to_check = 'administrator'; //Change to the slug of the role, i.e. administrator, editor, author, contributor, subscriber.
	if ( in_array( $role_to_check, (array) $user->roles ) ) {
		$rule_groups = array();
	}
	return $rule_groups;
}, 10, 2 );
