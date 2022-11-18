<?php
/**
 * Gravity Perks // Nested Forms // Add Child Entry on Render
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Programattically create and attach a child entry to a Nested Form field when the parent form is rendered.
 *
 * Please note: A new child entry will be added on every render. You will need to identify your own condition
 * for when a child entry should be generated and attached.
 */
// Update "123" to your form ID and "4" to your Nested Form field ID.
add_filter( 'gpnf_submitted_entry_ids_123_4', function( $entry_ids, $parent_form, $nested_form_field ) {

	$hash = gpnf_session_hash( $parent_form['id'] );

	$child_entry_id = gpnf_add_child_entry( $hash, $nested_form_field->id, array(
		// Update "1" to any field ID from your child form and "Second Choice" the value that should be saved to this field.
		'1' => 'Second Choice',
		// Update "2" to another field ID in your child field and "Second" to the value that should be saved to it.
		'2' => time(),
		// Add as many "field ID" => "value" pairs as required.
	), $parent_form['id'] );

	$session = new GPNF_Session( $parent_form['id'] );

	// Attach new child entry to the session.
	$session->add_child_entry( $child_entry_id );

	// Get all entry IDs from the session and return them.
	$session_entry_ids = $session->get( 'nested_entries' );
	if ( ! empty( $session_entry_ids[ $nested_form_field->id ] ) ) {
		$entry_ids = $session_entry_ids[ $nested_form_field->id ];
	}

	return $entry_ids;
}, 10, 3 );

if ( ! function_exists( 'gpnf_session_hash' ) ) {
	function gpnf_session_hash( $form_id ) {
		$session = new GPNF_Session( $form_id );
		return $session->get_runtime_hashcode();
	}
}

if ( ! function_exists( 'gpnf_add_child_entry' ) ) {
	/**
	 * @param int   $parent_entry_id      The ID of the entry to which this child entry should be attached.
	 * @param int   $nested_form_field_id The ID of the Nested Form field on the parent form to which this child entry should be attached.
	 * @param array $field_values         An array of field values that will be used to created the child entry (e.g. array( 1 => 'value' )).
	 * @param int   $parent_form_id       The ID of the parent entry's form. If not provided, will be looked up based on the provided parent entry ID.
	 */
	function gpnf_add_child_entry( $parent_entry_id, $nested_form_field_id, $field_values = array(), $parent_form_id = false ) {

		if ( ! $parent_form_id ) {
			$parent_entry   = GFAPI::get_entry( $parent_entry_id );
			$parent_form_id = $parent_entry['form_id'];
		}

		$nested_form_field = GFAPI::get_field( $parent_form_id, $nested_form_field_id );

		$new_child_entry = array_replace( array(
			// The ID of the parent form.
			'form_id'                               => $nested_form_field->gpnfForm,
			'created_by'                            => null,
			// The ID of the parent entry.
			GPNF_Entry::ENTRY_PARENT_KEY            => $parent_entry_id,
			// The ID of the parent form.
			GPNF_Entry::ENTRY_PARENT_FORM_KEY       => $parent_form_id,
			// The ID of the Nested Form field on the parent form.
			GPNF_Entry::ENTRY_NESTED_FORM_FIELD_KEY => $nested_form_field_id,
		), $field_values );

		$new_child_entry_id = GFAPI::add_entry( $new_child_entry );

		return $new_child_entry_id;
	}
}
