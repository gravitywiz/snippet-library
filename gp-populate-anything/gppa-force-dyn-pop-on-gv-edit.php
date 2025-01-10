<?php
/**
 * Gravity Perks // Populate Anything // Force Dynamic Population When Editing via GravityView
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * If you need to force repopulation when editing a child entry via GravityView, see:
 * https://github.com/gravitywiz/snippet-library/blob/master/gp-populate-anything/gppa-gpnf-force-dyn-pop-on-gv-edit.php
 */
add_filter( 'gravityview/edit_entry/field_value', function( $field_value, $field, $_this ) {
	// Start customizing
	$form_id               = 1;
	$field_ids_to_populate = array( 9, 10 );
	// End customizing

	if ( $field->formId == $form_id && in_array( $field->id, $field_ids_to_populate ) ) {
		$populated_field = gp_populate_anything()->populate_field( $field, $_this->form, gp_populate_anything()->get_posted_field_values( $_this->form ) );

		if ( $field->storageType === 'json' ) {
			$field_value = json_encode( $populated_field['field_value'] );
		} else {
			$field_value = $populated_field['field_value'];
		}

		$GLOBALS['gppa-field-values'][ $field->formId ][ $field->id ] = $field_value;
	}

	return $field_value;
}, 10, 3 );
