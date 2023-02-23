<?php

/**
 * Gravity Perks // Advanced Save and Continue // Use Submission Values for Draft Names
 *
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 *
 * Use draft submission values to populate the draft name.
 */
// Update "123" to your form ID.
add_filter( 'gpasc_draft_display_name_123', function( $display_name, $form_id, $resume_token_data ) {
	// Update "4" to the ID of the field to be used to set the draft name.
	$field_id          = 4;
	$submission_data   = GFFormsModel::get_draft_submission_values( $resume_token_data['token'] );
	$submission_values = json_decode( $submission_data['submission'], true );
	$submission_value  = rgar( $submission_values['submitted_values'], $field_id );
	// If the field is a single input field like a text field.
	if ( ! is_array( $submission_value ) ) {
		empty( $submission_value ) ? $display_name : $display_name .= ' - ' . $submission_value;
	} else {
		// If it's a multi-input field like a Name field.
		// This gets the first name like so field_id.3 and the last_name like so field_id.6.
		$full_name = $submission_value[ $field_id . '.3' ] . ' ' . $submission_value[ $field_id . '.6' ];
		empty( trim( $full_name ) ) ? $display_name : $display_name .= ' - ' . $full_name;
	}
	return $display_name;
}, 10, 3 );
