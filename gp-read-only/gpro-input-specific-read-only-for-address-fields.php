<?php
/**
 * Gravity Perks // Read Only // Input-specific Read Only for Address Fields
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 *
 * Instructions
 *
 * 1. Enable the "Read-only" setting in your Address field's field settings.
 *
 * 2. Install this snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 3. Update the snippet to apply to your form and field per the inline instructions.
 */
// Update "123" to your form ID and "4" to your Address field ID.
add_filter( 'gform_field_input_123_4', function( $input, $field, $value, $lead_id, $form_id ) {
	// Update these numbers to the input indexes that should NOT be read-only.
	$input_ids = array( 1, 2, 3, 5 );
	foreach ( $input_ids as $input_id ) {
		$input = str_replace( "readonly='readonly' type='text' name='input_{$field->id}.{$input_id}'", "type='text' name='input_{$field->id}.{$input_id}'", $input );
	}
	return $input;
}, 11, 5 );
