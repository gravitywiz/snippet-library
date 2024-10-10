<?php
/**
 * Gravity Perks // Entry Blocks // Allow Editing Entries by User Meta
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Use this snippet to allow users to edit entries via Entry Blocks based on the presence of the entry ID in their user meta.
 * Specify the desired user meta key within the snippet and simply save entry IDs (as an array) to that user meta key to allow
 * the user to edit those entries.
 *
 * Instructions:
 *
 * 1. Install this snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/
 *
 * 2. Update the snippet for your desired configuration based on the inline comments.
 */
add_filter( 'gpeb_can_user_edit_entry', function( $can_user_edit_entry, $entry, $current_user_id ) {
	$current_user = new WP_User( $current_user_id );
  // Update "entry_ids" to whatever user meta key you're storing entry IDs for which the user has permission to edit.
	if ( in_array( $entry['id'], (array) $current_user->get( 'entry_ids' ) ) ) {
		$can_user_edit_entry = true;
	}
	returN $can_user_edit_entry;
}, 10, 3 );
