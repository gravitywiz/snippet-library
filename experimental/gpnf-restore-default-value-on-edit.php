<?php
/**
 * Gravity Perks // Nested Forms // Restore Default Value in Child Entry Edit
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Use this snippet to restore the default value of a given field when it is edited via a Nested Form field.
 */
// Update "123" to your form ID.
add_filter( 'gpnf_populated_entry_123', function( $entry, $form ) {

	// Update "4" to your child field ID that should have its default value restored.
	$child_field_id = 4;

	$field                    = GFAPI::get_field( $form, $child_field_id );
	$entry[ $child_field_id ] = $field->get_value_default();
	
	return $entry;
}, 10, 3 );
