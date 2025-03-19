<?php
/**
 * Gravity Perks // Nested Forms // Set Created By Propery on Child Entries After User Account Registration.
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Set the created by property of the child entries that is embedded on a user registration form
 * with the user id of the account that is created after the parent form is submittted.
 *
 * Instructions:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Configure the snippet based on inline instructions.
 */
add_action( 'gform_user_registered', 'add_created_by_property', 10, 4 );
function add_created_by_property( $user_id, $feed, $entry, $user_pass ) {
	// Update '123' with the Id of the form.
	if ( $entry['form_id'] !== '123' ) {
		return;
	}

	$parent_entry  = new GPNF_Entry( $entry );
	$child_entries = $parent_entry->get_child_entries();

	foreach ( $child_entries as $child_entry ) {
		GFAPI::update_entry_property( $child_entry['id'], 'created_by', $user_id );
		$child_entry['created_by'] = $user_id;
	}
}
