<?php
/**
 * Gravity Perks // GP Limit Submissions // Apply Limit Collectively to Group of Forms
 * http://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 *
 * Instruction Video: https://www.loom.com/share/790781a22ff14d2f8023b7dd1adeb3eb
 */
add_filter( 'gpls_rule_groups', function( $rule_groups, $form_id ) {

	// Update "123" to the ID of the form that will share its feeds with the other forms.
	$primary_form_id = 123;

	// Update the following to the form IDs of each form that should share the limits of the primary form.
	$group_form_ids = array( 124, 125, 126 );

	// STOP! No need to edit below this line.
	$applicable_forms = array_merge( array( $primary_form_id ), $group_form_ids );
	if ( ! in_array( $form_id, $applicable_forms ) ) {
		return $rule_groups;
	}

	$rule_groups = array_merge( $rule_groups, GPLS_RuleGroup::load_by_form( $primary_form_id ) );
	foreach ( $rule_groups as $rule_group ) {
		$rule_group->applicable_forms = $applicable_forms;
	}

	return $rule_groups;
}, 10, 2 );
