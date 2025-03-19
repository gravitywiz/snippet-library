<?php
/**
 * Gravity Perks // Nested Forms // Delete Expired Orphan Child Entries (Instead of Trashing)
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Experimental Snippet 🧪
 */
add_action( 'gform_update_status', function( $entry_id, $new_status ) {
	if ( $new_status === 'trash' ) {
		$child_entry_expiration = gform_get_meta( $entry_id, GPNF_Entry::ENTRY_EXP_KEY );
		if ( $child_entry_expiration ) {
			GFAPI::delete_entry( $entry_id );
		}
	}
}, 10, 2 );
