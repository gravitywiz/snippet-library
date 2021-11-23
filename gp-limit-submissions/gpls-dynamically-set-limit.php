<?php
/**
 * Gravity Perks // Limit Submissions // Dynamically Set Limit
 * http://gravitywiz.com/gravity-forms-limit-submissions/
 */
// Update '123' to the Form ID
add_filter('gpls_rule_groups_123', function ( $rule_groups ) {

	foreach ( $rule_groups as &$rule_group ) {
		if ( $rule_group->name == 'Your Feed Name' ) {
			$rule_group->limit = 100;
		}
	}

	return $rule_groups;
});
