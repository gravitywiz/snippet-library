<?php
/**
 * Gravity Perks // Nested Forms // Force {Parent} Merge Tag Replacement on Submission
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/5ff99681acb6462ea9268e1ff30cd220
 *
 * Override all {Parent} merge tags when the parent form is submitted or a parent entry is updated.
 */
add_filter( 'gform_entry_post_save', 'gpnf_override_parent_merge_tags', 11, 2 );

add_action( 'gform_after_update_entry', function ( $form, $entry_id ) {
	$entry = GFAPI::get_entry( $entry_id );
	gpnf_override_parent_merge_tags( $entry, $form );
}, 11, 2 );

add_filter( 'gravityview-inline-edit/entry-updated', function( $return, $entry, $form_id ) {
	gpnf_override_parent_merge_tags( $entry, GFAPI::get_form( $form_id ) );
	return $return;
}, 10, 3 );

function gpnf_override_parent_merge_tags( $entry, $form ) {
	// Update '123' to the ID of the Child form. Set as false to apply to all child forms.
	$id_of_child_form = 123;
	// Updated '4, 5, 6' with the IDs of the fields you want to skip. Leave blank to apply to all fields.
	$exclude_field_ids = array( 4, 5, 6 );

	foreach ( $form['fields'] as $field ) {
		if ( $field->get_input_type() === 'form' ) {
			$child_form_id = $field->gpnfForm;
			if ( $id_of_child_form ) {
				if ( $child_form_id != $id_of_child_form ) {
					return $entry;
				}
			}
			$child_form = GFAPI::get_form( $child_form_id );
			foreach ( $child_form['fields'] as $child_field ) {
				if ( in_array( $child_field->id, $exclude_field_ids ) ) {
					continue;
				}
				if ( $child_field->get_entry_inputs() ) {
					foreach ( $child_field->get_entry_inputs() as $input ) {
						preg_match( '/{Parent:(.+)}/i', rgar( $input, 'defaultValue' ), $match );
						if ( $match ) {
							$value           = rgar( $entry, $match[1] );
							$child_entry_ids = explode( ',', rgar( $entry, $field->id ) );
							foreach ( $child_entry_ids as $child_entry_id ) {
								GFAPI::update_entry_field( $child_entry_id, $input['id'], $value );
							}
						}
					}
				} else {
					preg_match( '/{Parent:(.+)}/i', $child_field->defaultValue, $match );
					if ( $match ) {
						$value           = rgar( $entry, $match[1] );
						$child_entry_ids = explode( ',', rgar( $entry, $field->id ) );
						foreach ( $child_entry_ids as $child_entry_id ) {
							GFAPI::update_entry_field( $child_entry_id, $child_field->id, $value );
						}
					}
				}
			}
		}
	}

	return $entry;
}
