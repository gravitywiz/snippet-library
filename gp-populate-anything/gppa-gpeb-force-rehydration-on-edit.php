<?php
/**
 * Gravity Forms // Populate Anything + Entry Blocks // Force Rehydration on Edit
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Use this snippet to force fields to be rehydrated via Populate Anything when they are rendered in
 * the Entry Blocks edit form.
 *
 * Note: This snippet requires Populate Anything 2.0+.
 */
add_filter( 'gpeb_edit_form_entry', function( $entry ) {

	// Update "123" to your form ID.
	$form_id = 123;

	// Update (or remove) "4", "5", and "6" to the field IDs that should be rehydrated.
	$field_ids = array( 4, 5, 6 );

	if ( ! is_callable( array( 'GP_Populate_Anything', 'populate_field' ) ) || ! isset( $GLOBALS['gppa-field-values'] ) || $entry['form_id'] != $form_id ) {
		return $entry;
	}

	$form = GFAPI::get_form( $form_id );
	foreach ( $form['fields'] as &$field ) {
		if ( in_array( $field->id, $field_ids ) ) {
			$hydrated_field      = gp_populate_anything()->populate_field( $field, $form, array(), $entry );
			$entry[ $field->id ] = $hydrated_field['field_value'];
		}
	}

	return $entry;
}, 10, 2 );
