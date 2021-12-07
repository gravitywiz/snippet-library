<?php
/**
 * Gravity Perks // Populate Anything // Force Dynamic Population When Editing via Gravity View
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gravityview/edit_entry/field_value', function( $field_value, $field ) {
	// Update "123" to your form ID and "4" to your field ID.
	if ( $field->formId == 123 && $field->id == 4 ) {
		if ( isset( $GLOBALS['gppa-field-values'] ) ) {
			$hydrated_field = gp_populate_anything()->hydrate_field( $field, GFAPI::get_form( $field->formId ), array(), null, false );
			$field_value = $hydrated_field['field_value'];
		}
	}
	return $field_value;
}, 10, 2 );

/**
 * When editing a child entry in a Nested Form field via Gravity View, use this variation.
 * Remove this section if you are not using Nested Forms.
 */
// Update "124" to your parent form ID and "5" to your Nested Form field ID.
add_filter( 'gpnf_populated_entry_124_5', function( $entry, $form ) {
	if ( is_callable( 'gravityview_get_context' ) && gravityview_get_context() === 'edit' && isset( $GLOBALS['gppa-field-values'] ) ) {
		// Update "6" to the field ID you wish to force dynamic population for in the nested form.
		$field               = GFAPI::get_field( $form, 6 );
		$hydrated_field      = gp_populate_anything()->hydrate_field( $field, $form, array(), null, false );
		$entry[ $field->id ] = $hydrated_field['field_value'];
	}
	return $entry;
}, 10, 2 );
