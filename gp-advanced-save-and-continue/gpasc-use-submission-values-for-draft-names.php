<?php

/**
 * Gravity Perks // Advanced Save and Continue // Use Submission Values for Draft Names
 *
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 *
 * Use draft submission values to populate the draft name.
 */

add_filter( 'gpasc_draft_display_name', function( $display_name, $form_id, $resume_token_data ) {
	$submission_data   = GFFormsModel::get_draft_submission_values( $resume_token_data['token'] );
	$submission_values = json_decode( $submission_data['submission'], true );

	// submission values has a bunch of data on it including the values from each form field.

	// TODO return some concatenation of the submission values to use as the draft name.
	return $display_name;
}, 10, 3 );
