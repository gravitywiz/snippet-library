<?php
/**
 * Gravity Perks // Nested Forms // Duplicate Child Entries via Gravity Flow Form Connector
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This snippet duplicates child entries when a new entry is created via the Gravity Flow Form Connector.
 * The Nested Form field must be mapped to the same child form on the source and target forms.
 */
add_action( 'gravityflowformconnector_post_new_entry', function( $entry_id, $entry, $form, $step_new_entry ) {

	if ( ! class_exists( 'GPNF_Entry' ) || ! function_exists( 'gp_nested_forms' ) ) {
        return;
    }

	$new_entry = GFAPI::get_entry( $entry_id );

    // Loop through each field in the form
    foreach ( $form['fields'] as $field ) {

		// Check if it's a Nested Form field
        if ( $field->get_input_type() !== 'form' ) {
			continue;
        }

	    $child_entries = ( new GPNF_Entry( $new_entry ) )->get_child_entries( $field->id );
		if ( empty( $child_entries ) ) {
			continue;
		}

	    $duplicated_child_entries = array();

	    // Duplicate the child entries and associate them with this new entry
	    foreach ( $child_entries as $child_entry ) {

			$child_entry[ GPNF_Entry::ENTRY_PARENT_KEY ]            = $new_entry['id'];
			$child_entry[ GPNF_Entry::ENTRY_PARENT_FORM_KEY ]       = $new_entry['form_id'];
			$child_entry[ GPNF_Entry::ENTRY_NESTED_FORM_FIELD_KEY ] = $field->id;
			// @todo Add support for fetching Nested Form ID from target Nested Form field.
			//$child_entry['form_id']                                 = $field->gpnfForm;

		    $duplicated_child_entry     = GFAPI::add_entry( $child_entry );
		    $duplicated_child_entries[] = $duplicated_child_entry;
	    }

	    // Update Nested Form Field value on parent form to use the newly duplicated child entries.
	    GFAPI::update_entry_field( $new_entry['id'], $field->id, implode( ',', $duplicated_child_entries ) );

    }
}, 10, 4 );
