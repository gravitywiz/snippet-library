<?php
/**
 * Gravity Perks // Limit Submissions // Dynamically Set Limit by User ID
 * http://gravitywiz.com/gravity-forms-limit-submissions/
 */
// Update "123" to your form ID.
add_filter( 'gpls_rule_groups_123', function ( $rule_groups ) {
	// Update "234" to your excepted user ID. Add additional user IDs separated by a comma.
	if ( in_array( get_current_user_id(), array( 234 ) ) ) {
		// Update "5" to your desired limit for excepted users.
		$rule_groups[0]->limit = 5;
	}
	return $rule_groups;
} );
