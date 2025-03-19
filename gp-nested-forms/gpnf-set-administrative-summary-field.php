<?php
/**
 * Gravity Perks // Nested Forms // Set Administrative Field as Summary Field on Entry Detail
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
// Update "123" to your parent form ID.
add_action( 'gform_admin_pre_render_123', function( $form ) {

	// Update "4" to your Nested Form field ID.
	$nested_form_field_id = 4;
	// Update "5" to your administrative field ID. You can add multiple fields by adding more IDs to the array.
	$add_summary_field_ids = array( 5 );

	if ( GFForms::get_page() !== 'entry_detail' ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {
		if ( $field->id == $nested_form_field_id ) {
			$field->gpnfFields = array_merge( $field->gpnfFields, $add_summary_field_ids );
		}
	}

	return $form;
} );
