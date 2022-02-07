<?php
/**
 * Gravity Perks // Nested Forms // Don't Trash Child Entries
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * By default, Nested Forms will trash child entries when the parent entry is trashed. Use this snippet to prevent this
 * default behavior.
 */
add_action( 'init', function() {
	if ( is_callable( 'gp_nested_forms' ) ) {
		remove_action( 'gform_delete_entry', array( gp_nested_forms(), 'child_entry_delete' ) );
		remove_action( 'gform_update_status', array( gp_nested_forms(), 'child_entry_trash_manage' ) );
	}
}, 16 );
