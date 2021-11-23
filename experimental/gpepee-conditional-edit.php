<?php
/**
 * Gravity Perks // Easy Passthrough Edit Entry // Conditional Edit
 * https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/
 * 
 * Create a field on your form and check for a specific value on that field to determine if the passed 
 * through entry should be edited or if a new entry should be created.
 */
// Update "123" to the form you are using to edit entries.
add_filter( 'gpepee_edit_entry_id_123', function( $update_entry_id, $form_id ) {
	// Update "4" to the field ID you want to check the value of.
	if ( rgpost( 'input_4' ) === 'Add New' ) {
		$update_entry_id = false;
	}
	return $update_entry_id;
}, 10, 2 );
