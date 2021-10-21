<?php
/**
 * Gravity Wiz // Gravity Forms // Populate Range by Another Field
 * https://gravitywiz.com/
 *
 * Set a field's minimum and maximum range by the values of other fields. Currently, this will only work with default
 * values. If the value changes after the form has been rendered, it will still generate a validation error but the
 * range message that displays below the input will not update automatically.
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_render_123', 'gw_set_range', 10, 3 );
add_filter( 'gform_pre_process_123', 'gw_set_range', 10, 3 );
function gw_set_range( $form, $ajax, $field_values ) {

	foreach( $form['fields'] as &$field ) {
		// Update "4" to the ID of the field that should have its range modified.
		if( $field->id == 4 ) {
			// Update "5" to the ID of the field whose value should be used for the minimum range.
			$min_field       = GFAPI::get_field( $form, 5 );
			$field->rangeMin = GFFormsModel::get_field_value( $min_field, $field_values );
			// Update "6" to the ID of the field whose value should be used for the maximum range.
			$max_field       = GFAPI::get_field( $form, 6 );
			$field->rangeMax = GFFormsModel::get_field_value( $max_field, $field_values );
		}
	}

	return $form;
}
