<?php
/**
 * Gravity Perks // Inventory // Hide Form if Preselected Choice is Exhausted
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 * 
 * This snippet allows you to hide the form if you're using dynamic population to preselect a choice
 * and that choice's inventory is exhausted.
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_render_123', function( $form, $ajax, $field_values ) {
	add_filter( "gform_get_form_filter_{$form['id']}", function( $markup, $form ) use ( $field_values ) {
		// Update "4" to the ID of your inventory-enabled field.
		$field = GFAPI::get_field( $form, 4 );
		$value = GFFormsModel::get_field_value( $field, $field_values );
		$choice = rgar( gp_inventory_type_choices()->get_selected_choices( $field, $value ), 0 );
		$count  = gp_inventory_type_choices()->get_choice_count( $value, $field, $field->formId );
		$limit  = gp_inventory_type_choices()->get_choice_inventory_limit( $choice, $field, $form );
		if ( $count >= $limit ) {
			$markup = gp_inventory_type_choices()->get_inventory_exhausted_message( $field );
		}
		return $markup;
	}, 10, 2 );
	return $form;
}, 10, 3 );
