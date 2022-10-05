<?php
/**
 * Gravity Perks // Nested Forms // Force {Parent} Merge Tag Replacement on Submission
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/1ccbaa94d6b94f0f97829916a8396ac7
 *
 * Override all {Parent} merge tags when the parent form is submitted or a parent entry is updated.
 */
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
						gpnf_override_child_entry_input_value( $entry, $field, $input['id'], rgar( $input, 'defaultValue' ) );
					}
				} else {
					gpnf_override_child_entry_input_value( $entry, $field, $child_field->id, $child_field->defaultValue );
				}
			}

			// Reprocess calculations based off of the potentially changed parent value.
			$child_entry_ids = explode( ',', rgar( $entry, $field->id ) );

			foreach ( $child_entry_ids as $child_entry_id ) {
				foreach ( $child_form['fields'] as $child_field ) {
					if ( in_array( $child_field->id, $exclude_field_ids ) ) {
						continue;
					}

					if ( ! $child_field->has_calculation() ) {
						continue;
					}

					// @todo do calculated product fields need to be accounted for differently?
					$child_entry      = GFAPI::get_entry( $child_entry_id );
					$calculated_value = $child_field->get_value_save_entry( null, $child_form, $child_field->id, $child_entry_id, $child_entry );
					GFAPI::update_entry_field( $child_entry_id, $child_field->id, $calculated_value );
				}
			}
		}
	}

	return $entry;
}

function gpnf_override_child_entry_input_value( $entry, $field, $input_id, $default_value ) {

	preg_match_all( '/{Parent:(\d+(\.\d+)?)[^}]*}/i', $default_value, $matches, PREG_SET_ORDER );
	if ( empty( $matches ) ) {
		return;
	}

	$value = $default_value;
	foreach ( $matches as $match ) {
		$value = str_replace( $match[0], rgar( $entry, $match[1] ), $value );
	}

	$child_entry_ids = explode( ',', rgar( $entry, $field->id ) );
	foreach ( $child_entry_ids as $child_entry_id ) {
		GFAPI::update_entry_field( $child_entry_id, $input_id, $value );
	}

}

add_filter( 'gform_entry_post_save', 'gpnf_override_parent_merge_tags', 11, 2 );

add_action( 'gform_after_update_entry', function ( $form, $entry_id ) {
	$entry = GFAPI::get_entry( $entry_id );
	gpnf_override_parent_merge_tags( $entry, $form );
}, 11, 2 );

add_filter( 'gravityview-inline-edit/entry-updated', function( $return, $entry, $form_id ) {
	gpnf_override_parent_merge_tags( $entry, GFAPI::get_form( $form_id ) );
	return $return;
}, 10, 3 );
