<?php
/**
 * Gravity Perks // GP Limit Submissions // Disable Limit Feeds when Editing via Gravity View
 * http://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */
add_filter( 'gpls_rule_groups', function( $rule_groups, $form_id ) {

	if( is_callable( 'gravityview_get_context' ) && gravityview_get_context() == 'edit' ) {
		$rule_groups = array();
	}

	return $rule_groups;
}, 10, 2 );