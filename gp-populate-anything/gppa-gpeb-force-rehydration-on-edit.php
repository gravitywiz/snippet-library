<?php
/**
 * Gravity Forms // Populate Anything + Entry Blocks // Force Rehydration on Edit
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Use this snippet to force fields to be rehydrated via Populate Anything when they are rendered in 
 * the Entry Blocks edit form.
 */
add_filter( 'gpeb_edit_form_entry', function( $entry ) {

	$form_id = 34;
	$field_ids = array( 1, 3, 4 );

	if ( ! isset( $GLOBALS['gppa-field-values'] ) || $entry['form_id'] != $form_id ) {
		return $entry;
	}

	$form = GFAPI::get_form( $form_id );
	foreach ( $form['fields'] as &$field ) {
		if ( in_array( $field->id, $field_ids ) ) {
			$hydrated_field     = gp_populate_anything()->hydrate_field( $field, $form, array(), null );
			$entry[ $field->id ] = $hydrated_field['field_value'];
		}
	}

	return $entry;
}, 10, 2 );
