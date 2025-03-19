<?php
/**
 * Gravity Perks // GP Entry Blocks // Redirect to edit newly duplicated entries
 *
 * Installation:
 *  See https://github.com/gravitywiz/snippet-library/blob/master/gp-entry-blocks/gpeb-show-all-entries-to-admin.php
 */
add_action( 'gpeb_entry_duplicated', function( $entry ) {
	wp_safe_redirect( add_query_arg( array(
		'edit_entry' => $entry['id'],
	), \GP_Entry_Blocks\cleaned_current_url() ) );
});
