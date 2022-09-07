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
	$date_created = new DateTime( $entry['date_created'] );
	$locked_date = new DateTime( '2022-08-24' );
	if ( $date_created < $locked_date ) {
		$can_user_edit_entry = false;
	}
	return $can_user_edit_entry;
}, 10, 2 );
