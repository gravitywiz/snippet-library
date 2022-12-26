<?php
/**
 * Gravity Perks // Limit Submissions // Set Limit by Custom Field
 * http://gravitywiz.com/gravity-forms-limit-submissions/
 */
// Update "123" to your form ID.
add_filter( 'gpls_rule_groups_123', function ( $rule_groups ) {
  
  // Update "Your Feed Name" to the name of your Limit Submissions feed.
  $feed_name = 'My Feed Name';
  
  // Update "my_custom_limit" to your custom field key.  
  $custom_field = 'my_custom_limit';
  
  $post_id = get_queried_object_id();
  if ( ! $post_id ) {
    return $rule_groups;
  }
  
	foreach ( $rule_groups as &$rule_group ) {
		if ( $rule_group->name == $feed_name ) {
			$rule_group->limit = get_post_meta( $post_id, $custom_field, true );
		}
	}

	return $rule_groups;
} );
