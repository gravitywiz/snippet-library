<?php
/**
 * Gravity Perks // Nested Forms // Auto-attach Child Entries to Parent when Editing via GravityView
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * By default, when adding a new child entry to a Nested Form field while editing a parent entry via GravityView, the
 * child entry is saved to the session and will not be attached to the parent entry unless you click "Update" on the
 * parent entry form.
 *
 * Use this snippet to automatically attach the child entry to the parent as soon as the child form is submitted.
 *
 * To preserve the default behavior but warn users that newly added child entries have not been attached to the parent, see:
 * https://github.com/gravitywiz/snippet-library/blob/master/gp-nested-forms/gpnf-gv-unsaved-child-entries-warning.js
 */
add_filter( 'gpnf_set_parent_entry_id', function( $parent_entry_id ) {
	if ( ! $parent_entry_id && is_callable( 'gravityview_get_context' ) && gravityview_get_context() === 'edit' ) {
		$parent_entry_id = GravityView_frontend::is_single_entry();
	}
	return $parent_entry_id;
} );
