<?php
/**
 * Gravity Perks // Nested Forms // Disable Sessions
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/348e9e70999a43eaaf73cf0c820161aa
 *
 * Given how much data can be captured in a child entry, Nested Forms provides a safety net by storing
 * submitted entry IDs in a cookie so that if the user accidently refreshes the page or closes the tab
 * their submitted entries can be restored.
 *
 * If you would prefer that Nested Form fields function like other fields where the data is not preserved,
 * use this snippet to disable Nested Forms sessions.
 */
add_action( 'wp_ajax_gpnf_session', 'gw_gpnf_disable_session', 9 );
add_action( 'wp_ajax_nopriv_gpnf_session', 'gw_gpnf_disable_session', 9 );
function gw_gpnf_disable_session() {
	remove_action( 'wp_ajax_gpnf_session', array( gp_nested_forms(), 'ajax_session' ) );
	remove_action( 'wp_ajax_nopriv_gpnf_session', array( gp_nested_forms(), 'ajax_session' ) );
	// Delete previous stored session.
	$session = new GPNF_Session( '4' ); // Update "4" to the ID of the parent form.
	$session->delete_cookie();
}

// Allow non-admin users to edit or delete entry.
add_filter( 'gpnf_can_user_edit_entry', function( $can_user_edit_entry, $entry ) {
	// Update "5" to the ID of the child form.
	if ( $entry['form_id'] == '5' ) {
		return true;
	}

	return $can_user_edit_entry;
}, 10 , 2);
