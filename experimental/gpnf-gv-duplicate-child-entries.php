<?php
/**
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * https://gravityview.co/
 *
 * Installation Instructions: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * Duplicate child entries when parent forms are duplicated using Gravity View.
 *
 * Configuring:
 *  * Update $parent_form_ids to match the form IDs that need to have their Nested Form field child entries duplicated
 *  * Updated $parent_form_field_ids to include the Nested Form field IDs that should have their child entries duplicated
 *
 * As an example, this snippet is configured to duplicate child entries for Nested Form fields 1 and 2 in form 17.
 */
add_action( 'gravityview/duplicate-entry/duplicated', function ( $duplicated_entry, $original_entry ) {
	if ( ! class_exists( 'GPNF_Entry' ) || ! function_exists( 'gp_nested_forms' ) ) {
		return;
	}

	/**
	 * Important! Update these two variables.
	 */
	$parent_form_ids       = array( 17 );
	$parent_form_field_ids = array( 1, 2 );

	if ( ! in_array( $duplicated_entry['form_id'], $parent_form_ids ) ) {
		return;
	}

	$parent_form = GFAPI::get_form( $original_entry['form_id'] );

	/**
	 * Loop through all fields in the parent form to find Nested Form fields that need their child entries
	 * duplicated.
	 *
	 * Only the Nested Form fields specified in $parent_form_field_ids above will have their child entries duplicated.
	 */
	foreach ( $parent_form['fields'] as $field ) {
		if ( $field->type !== 'form' || ! in_array( $field->id, $parent_form_field_ids ) ) {
			continue;
		}

		/**
		 * Loop through child entries for the current Nested Form field and duplicate them.
		 */
		$duplicated_child_entries = array();

		$value         = GFFormsModel::get_lead_field_value( $original_entry, $field );
		$child_entries = gp_nested_forms()->get_entries( $value );

		foreach ( $child_entries as $child_entry ) {
			// Change the entry parent ID to the duplicated parent entry.
			$child_entry[ GPNF_Entry::ENTRY_PARENT_KEY ] = $duplicated_entry['id'];

			$duplicated_child_entry     = GFAPI::add_entry( $child_entry );
			$duplicated_child_entries[] = $duplicated_child_entry;
		}

		/**
		 * Update Nested Form Field value on parent form to use the newly duplicated child entries.
		 */
		$duplicated_entry[ $field->id ] = implode( ',', $duplicated_child_entries );

		GFAPI::update_entry( $duplicated_entry, $duplicated_entry['id'] );
	}
}, 10, 2 );
