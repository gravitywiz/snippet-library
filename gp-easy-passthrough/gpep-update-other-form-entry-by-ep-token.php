<?php
/**
 * Gravity Perks // Easy Passthrough // Update Other Entry on Submission
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 * 
 * Instruction Video: Coming soon...
 * 
 * Update a field value of an entry on Form A when Form B is submitted based on populated
 * Easy Passthrough token.
 *
 * This technique provides a secure way to allow users to modify specific information on a previous
 * entry without exposing an entry ID. Exposing the entry ID can be a security risk as it is highly
 * predictable, potentially allowing bad actors to act on entries that do not belong to them.
 *
 * Instructions
 *
 * 1. Add a Hidden field to Form B that will be used to capture an Easy Passthrough token.
 * 
 * 2. Configure an Easy Passthrough feed on Form B to map data from Form A.
 * 
 * 3. In the "Map Entry Meta" section, map the "Easy Passthrough Token" from Form A to the 
 *    field you've created to capture it on Form B.
 *
 * 4. Install and configure this snippet per the inline instructions.
 */
// Update "123" to the form ID that should trigger an update to the other form's entry.
add_action( 'gform_after_submission_123', function( $entry, $form ) {

	// Update "4" to the ID of the field that is populated with the EP token.
	$token_field_id  = 4;

	// Update "5" to the ID of the field that should be updated on the other form's entry.
	$target_field_id = 5;

	// Update "Canceled" to the value that should be updated on the other form's entry.
	$update_value    = 'Canceled';

	$token        = $entry[ $token_field_id ];
	$source_entry = gp_easy_passthrough()->get_entry_for_token( $token );
	if ( $source_entry ) {
		GFAPI::update_entry_field( $source_entry['id'], $target_field_id, $update_value );
	}

}, 10, 2 );
