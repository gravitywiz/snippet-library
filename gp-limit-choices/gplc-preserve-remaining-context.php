<?php
/**
 * Gravity Perks // GP Limit Choices // Preserve Remaining Count Conditional Logic Context
 * https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 *
 * Limit Choices provides a useful feature for using the remaining count for a selected choice in conditional logic.
 * Currently, this option does not preserve it's context for the entire submission flow. For example, if there is one
 * "spot" of a given choice remaining, the conditional logic on the frontend will evaluate with a remaining count of 1.
 * However, on submission that spot is consumed and so the conditional logic will then evaluate with 0 remaining.
 *
 * This stop-gap solution allows you to save the remaining count at the time of submission. You can then use this field
 * to create conditional logic rules in post-submission contexts (i.e. Confirmations, Notifications, feeds).
 *
 * Instructions
 *
 * 1. Add a Hidden field to your form to capture the remaining count at the time of submission.
 * 2. Copy and paste this snippet into your theme's functions.php file.
 * 3. Update the snippet per the inline instructions.
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_submission_123', function( $form ) {

	// Update "4" to the ID of the field with Limit Choices enabled.
	$choice_field_id = 4;

	// Update "5" to the ID of a Hidden field on your form to be used to store the remaining count on submission.
	$remaining_field_id = 5;

	$choice_count = GP_Limit_Choices::get_entries_left( $form['id'], $choice_field_id, rgpost( "input_{$choice_field_id}" ) );

	$_POST[ "input_{$remaining_field_id}" ] = $choice_count;

} );
