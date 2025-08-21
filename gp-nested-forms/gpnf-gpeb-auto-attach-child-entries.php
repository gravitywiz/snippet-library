<?php
/**
 * Gravity Perks // Nested Forms // Auto-attach Child Entries to Parent when Editing via GP Entry Blocks.
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * By default, when adding a new child entry to a Nested Form field while editing a parent entry via GP Entry Blocks, the
 * child entry is saved to the session and will not be attached to the parent entry unless you click "Update" on the
 * parent entry form.
 *
 * Use this snippet to automatically attach the child entry to the parent as soon as the child form is submitted.
 */
add_filter( 'gpnf_set_parent_entry_id', function( $parent_entry_id ) {
	if ( rgget( 'edit_entry' ) ) {
		$parent_entry_id = rgget( 'edit_entry' );
	}
	return $parent_entry_id;
} );
