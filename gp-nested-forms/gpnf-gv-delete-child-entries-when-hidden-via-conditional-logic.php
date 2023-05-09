<?php
/**
 * Gravity Perks // Nested Forms // Delete Child Entries for Nested Form Fields Hidden via Conditional Logic when Editing via GravityView.
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_action( 'gravityview/edit_entry/after_update', function ( $form, $entry_id, $object ) {

	/**
	 * Nested Forms field values are set dynamically but during a submission it always honors the $_POST. We need to
	 * temporarily overwrite the $_POST values to trick GFAPI::get_entry() into returning the correct values.
	 */
	$orig_post = $_POST;
	$_POST = array();

	$entry = GFAPI::get_entry( $entry_id );

	$_POST = $orig_post;

	foreach ( $form['fields'] as $field ) {
		if ( $field->type == 'form' ) {
			$is_field_hidden = GFFormsModel::is_field_hidden( $form, $field, array(), $entry );
			if ( $is_field_hidden ) {
				// Delete child entries if the Nested Form field is hidden.
				$parent_entry = new GPNF_Entry( $entry );
				$parent_entry->delete_children();
			}
		}
	}

}, 10, 3 );
