<?php
/**
 * Gravity Perks // Entry Blocks // Change Filter Field Type
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * This snippet demonstrates how you can change the field type for filter. In this example, we convert a Checkbox field
 * to a Text field for use in the Filter form. This is useful if your field has lots of options but you'd rather let the
 * user search by text instead of displaying all the checkboxes in your filter field.
 */
add_filter( 'gpeb_filter_form_field', function( $field ) {
	// Update "123" to your form ID 
	if ( $field->formId == 123 && $field->id == 4 ) {
		$field->type = 'text';
		$field->choices = null;
		$field->inputs = null;
		$field = GF_Fields::create( $field );
	}
	return $field;
} );
