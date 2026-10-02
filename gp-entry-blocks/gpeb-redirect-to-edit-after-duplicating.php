<?php
/**
 * Gravity Perks // GP Entry Blocks // Redirect to edit newly duplicated entries
 *
 * Installation:
 *  See https://github.com/gravitywiz/snippet-library/blob/master/gp-entry-blocks/gpeb-show-all-entries-to-admin.php
 */
add_action( 'gpeb_entry_duplicated', function( $entry ) {

	// Fetch the newly created entry id.
	$new_entry_id = rgar( $GLOBALS, 'gpeb_duplicated_entry' );
	if ( ! $new_entry_id ) {
		return;
	}

	wp_safe_redirect( add_query_arg( array(
		'edit_entry' => $new_entry_id,
	), \GP_Entry_Blocks\cleaned_current_url() ) );
});
