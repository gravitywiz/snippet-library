<?php
/**
 * Gravity Perks // Nested Forms // Auto-attach Child Entries to Parent when Editing via Gravity Flow.
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * By default, when adding a new child entry to a Nested Form field while editing a parent entry via Gravity Flow, the
 * child entry is saved to the session and will not be attached to the parent entry unless you click "Submit" on the
 * workflow.
 *
 * Use this snippet to automatically attach the child entry to the parent as soon as the child form is submitted.
 */
add_filter( 'gpnf_set_parent_entry_id', function( $parent_entry_id ) {
	if ( ! $parent_entry_id && is_callable( 'gravity_flow' ) && gravity_flow()->is_workflow_detail_page() ) {
		$parent_entry_id = rgget( 'lid' ) ? rgget( 'lid' ) : $parent_entry_id;
	}
	return $parent_entry_id;
} );
