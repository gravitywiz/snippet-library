<?php
/**
 * Gravity Perks // Nested Forms // Force {Parent} Merge Tag Replacement on Submission
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Override all {Parent} merge tags when the parent form is submitted or a parent entry is updated.
 */
add_filter( 'gform_entry_post_save', 'gpnf_override_parent_merge_tags', 11, 2 );
add_action( 'gform_after_update_entry', function( $form, $entry_id ) {
	$entry = GFAPI::get_entry( $entry_id );
	gpnf_override_parent_merge_tags( $entry, $form );
}, 11, 2 );

function gpnf_override_parent_merge_tags( $entry, $form ) {

	foreach ( $form['fields'] as $field ) {
		if ( $field->get_input_type() === 'form' ) {
			$child_form_id = $field->gpnfForm;
			$child_form    = GFAPI::get_form( $child_form_id );
			foreach ( $child_form['fields'] as $child_field ) {
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
