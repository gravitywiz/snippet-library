<?php
/**
 * Gravity Perks // Nested Forms + Read Only // Make Fields Read-Only When Editing Child Entry
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Make specific fields read-only only when editing a child entry in a Nested Form field.
 * The fields will be editable when first creating the child entry, but read-only when editing.
 *
 * Instruction Video: https://www.loom.com/share/90fa28244ac54c7991a94a30b545ba63
 *
 * Requires Gravity Perks Read Only plugin to be installed and activated.
 */
// Update "123" to your child form ID and the array to your target field IDs.
add_filter( 'gform_pre_render_123', function( $form ) {

	if ( ! isset( $GLOBALS['gpnf_current_edit_entry'] ) ) {
		return $form;
	}

	// TODO: Field IDs that should be read-only when editing (add/remove field IDs as needed)
	$target_field_ids = array( 1, 2, 3 );

	// Find the target fields and make them read-only
	foreach ( $form['fields'] as &$field ) {
		if ( in_array( $field->id, $target_field_ids ) ) {
			$field->gwreadonly_enable = true;
		}
	}

	return $form;
} );
