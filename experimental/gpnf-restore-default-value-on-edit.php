<?php
/**
 * Gravity Perks // Nested Forms // Restore Default Value in Child Entry Edit
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Use this snippet to restore the default value of a given field when it is edited via a Nested Form field.
 */
// Update "123" to your parent form ID and "4" to your Nested Form field ID.
add_filter( 'gpnf_populated_entry_123_4', function( $entry, $form ) {

	// Update "5" to your child field ID that should have its default value restored.
	$child_field_id = 5;

	$field                    = GFAPI::get_field( $form, $child_field_id );
	$entry[ $child_field_id ] = $field->get_value_default();

	return $entry;
}, 10, 3 );
