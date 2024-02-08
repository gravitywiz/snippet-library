<?php
/**
 * Gravity Perks // Limit Submissions // Dynamically Set Limit by User Meta
 * http://gravitywiz.com/gravity-forms-limit-submissions/
 */
// Update "123" to your form ID.
add_filter( 'gpls_rule_groups_123', function ( $rule_groups ) {
  // Update "my_meta_key" to the meta key that should contain the number of times the form can be submitted.
	$rule_groups[0]->limit = (int) get_user_meta( get_current_user_id(), 'my_meta_key', true );
	return $rule_groups;
} );
