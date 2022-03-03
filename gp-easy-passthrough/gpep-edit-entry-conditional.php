<?php
/**
 * Gravity Perks // Easy Passthrough // Edit Entry Conditionally
 * https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/
 */
// Update "123" to your form ID.
add_filter( 'gpepee_edit_entry_id_123', function( $update_entry_id, $form_id ) {
	// Update "4" to the field ID you want to check the value of.
	if ( rgpost( 'input_4' ) === 'Add New' ) {
		$update_entry_id = false;
	}
	return $update_entry_id;
}, 10, 2 );
