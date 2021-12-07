<?php
/**
 * Gravity Perks // Populate Anything // Force Dynamic Population When Editing via GravityView
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * If you need to force repopulation when editing a child entry via GravityView, see:
 * https://github.com/gravitywiz/snippet-library/blob/master/gp-populate-anything/gppa-gpnf-force-dyn-pop-on-gv-edit.php
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
