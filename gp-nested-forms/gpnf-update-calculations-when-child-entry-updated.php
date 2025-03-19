<?php
/**
 * Gravity Perks // Nested Forms // Update calculations when child entry statuses are updated
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_action( 'gform_update_status', function ( $entry_id, $new_status, $old_status ) {
	$entry = new GPNF_Entry( $entry_id );

	// Updating the child entry status (trashed/spammed/restored), update formula calculations (if any).
	$parent_entry_id   = $entry->get_entry()['gpnf_entry_parent'];
	$parent_entry      = GFAPI::get_entry( $parent_entry_id );
	$parent_form_id    = $entry->get_entry()['gpnf_entry_parent_form'];
	$parent_form       = GFAPI::get_form( $parent_form_id );
	$nested_form_field = $entry->get_entry()['gpnf_entry_nested_form_field'];

	// Check for merge tag formula in use on each parent field.
	foreach ( $parent_form['fields'] as $field ) {
		$formula = rgar( $field, 'calculationFormula' );
		// Process the formula.
		$formula = GFCommon::calculate( $field, $parent_form, $parent_entry );
		// Get any successfully updated value.
		if ( ! empty( $formula ) ) {
			$parent_entry[ $field['id'] ] = $formula;
		}
	}

	GFAPI::update_entry( $parent_entry );
}, 12, 3 );
