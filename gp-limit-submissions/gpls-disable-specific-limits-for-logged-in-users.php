<?php
/**
 * Gravity Perks // GP Limit Submissions // Disable Specific Limits for Logged In Users
 * https://gravitywiz.com/documentaiton/gravity-forms-limit-submissions/
 *
 * Instructions:
 * 
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/
 *
 * 2. Configure the snippet based on inline instructions.
 */
// Update `123` to your form ID
add_filter( 'gpls_rule_groups_123', function( $rule_groups, $form_id ) {

  if( ! is_user_logged_in() ){
    return $rule_groups;
  }

  // Enter a comma separated list of GPLS feed IDs you need to disable
  $rules_to_disable = array( 68 );

  $new_rules = array();

	foreach( $rule_groups as $rule ){
		if( ! in_array( $rule->feed_id, $rules_to_disable ) ){
			$new_rules[] = $rule;
		}
	}

	return $new_rules;

}, 10, 2 );
