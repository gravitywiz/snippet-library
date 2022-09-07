<?php
/**
 * Gravity Perks // Nested Forms // Lock Child Entries by Date
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * 
 * When editing a parent entry via Entry Blocks or GravityView, this snippet will allow you
 * to prevent child entries from being edited (or deleted) after a given date. 
 *
 * This snippet leaves much to be desired in regards to UX as users will still be able to click 
 * on the "Edit" and "Delete" buttons. Clicking "Edit" will load the modal with a no access message.
 * Clicking "Delete" will do nothing.
 */
add_filter( 'gpnf_can_user_edit_entry', function( $can_user_edit_entry, $entry ) {
	
	// Update "2022-09-01" to your desired lockout date in YYYY-MM-DD format.
	$locked_date  = new DateTime( '2022-09-01' );
	
	// Update "123" to the parent form ID for which child entries should be locked.
	$target_parent_form_id = 123;
	
	$parent_form_id = rgpost( 'gpnf_parent_form_id' );
	if ( ! $parent_form_id || $parent_form_id != $target_parent_form_id ) {
		return $can_user_edit_entry;
	}
	
	$date_created = new DateTime( $entry['date_created'] );
	if ( $date_created < $locked_date ) {
		$can_user_edit_entry = false;
	}
	
	return $can_user_edit_entry;
}, 10, 2 );
