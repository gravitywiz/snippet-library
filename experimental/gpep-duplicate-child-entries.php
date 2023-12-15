<?php
/**
 * Gravity Perks // Easy Passthrough + Nested Forms // Duplicate child entries on passthrough
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * Installation Instructions: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * Duplicate child entries when a parent form is passed through using Easy Passthrough.
 *
 * Configuring:
 *  * Update $parent_form_ids to match the form IDs that need to have their Nested Form field child entries duplicated
 *  * Update $parent_form_field_ids to include the Nested Form field IDs that should have their child entries duplicated
 *
 * As an example, this snippet is configured to duplicate child entries for Nested Form fields 4 and 5 in form 123.
 */
add_filter( 'gpep_target_field_value', function ( $field_value, $form_id, $target_field_id, $source_field ) {

	/**
	 * Important! Update these two variables.
	 */
	$parent_form_ids       = array( 123 );
	$parent_form_field_ids = array( 4, 5 );

	if ( ! class_exists( 'GPNF_Entry' ) || ! function_exists( 'gp_nested_forms' ) ) {
		return $field_value;
	}

	if ( ! in_array( $form_id, $parent_form_ids ) ) {
		return $field_value;
	}

	// Ensure both the source field and target field are Nested Form fields.
	if ( $source_field->type !== 'form' || $target_field_id !== $source_field->id ) {
		return $field_value;
	}

	// Ensure that the source field matches one of the Nested Form fields specified in $parent_form_field_ids.
	if ( ! in_array( $source_field->id, $parent_form_field_ids ) ) {
		return $field_value;
	}

	/**
	 * Skip duplicating entries if the child entries are already temporary.
	 *
	 * @var GPNF_Session
	 */
	$session = new GPNF_Session( $form_id );
	$session->set_session_data();

	$cookie = $session->get_cookie();

	if ( rgars( $cookie, 'nested_entries/' . $target_field_id ) ) {
		return '';
	}

	/**
	 * Loop through child entries for the current Nested Form field and duplicate them.
	 */
	$duplicated_child_entries = array();

	$child_entries = gp_nested_forms()->get_entries( $field_value );

	foreach ( $child_entries as $child_entry ) {
		$duplicated_child_entry = GFAPI::add_entry( array_replace( $child_entry, array(
			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			'date_created'                          => date( 'Y-m-d H:i:s' ),
			GPNF_Entry::ENTRY_PARENT_KEY            => $session->get_runtime_hashcode(),
			GPNF_Entry::ENTRY_PARENT_FORM_KEY       => $form_id,
			GPNF_Entry::ENTRY_NESTED_FORM_FIELD_KEY => $target_field_id,
		) ) );

		// Attach session meta to child entry.
		$session->add_child_entry( $duplicated_child_entry );

		$duplicated_child_entries[] = $duplicated_child_entry;
	}

	return implode( ',', $duplicated_child_entries );
}, 10, 4 );
