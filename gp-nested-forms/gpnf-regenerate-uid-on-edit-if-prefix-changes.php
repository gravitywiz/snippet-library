<?php
/**
 * Gravity Perks // Nested Forms + Unique ID // Regenerate UID if prefix input value changes when editing.
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * http://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Instructions:
 *  1. https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *  2. Update variables in variable section below to match your form.
 */
add_filter( 'gform_entry_id_pre_save_lead', function ( $entry_id, $form ) {
	/* Variables */
	$child_form_id      = 13;
	$unique_id_field_id = 3;
	$prefix_field_id    = 4;

	if ( $form['id'] !== $child_form_id ) {
		return $entry_id;
	}

	/* Do not perform this logic for new entries. */
	if ( ! $entry_id ) {
		return $entry_id;
	}

	$submitted_prefix_value = rgpost( 'input_' . $prefix_field_id );
	$existing_entry         = GFAPI::get_entry( $entry_id );
	$existing_prefix_value  = rgar( $existing_entry, $prefix_field_id );

	/* Do nothing if the prefix is the same. */
	if ( $submitted_prefix_value == $existing_prefix_value ) {
		return $entry_id;
	}

	/* Unset the existing unique ID so it can be regenerated. */
	if ( isset( $existing_entry[ $unique_id_field_id ] ) ) {
		unset( $existing_entry[ $unique_id_field_id ] );
	}

	/* Unset the POSTed unique ID value so it doesn't replace when we just saved. */
	if ( isset( $_POST[ 'input_' . $unique_id_field_id ] ) ) {
		unset( $_POST[ 'input_' . $unique_id_field_id ] );
	}

	gp_unique_id_field()->populate_field_value( $existing_entry, $form, true );

	return $entry_id;
}, 15, 2 );
