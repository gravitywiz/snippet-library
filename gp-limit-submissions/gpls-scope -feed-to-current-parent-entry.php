<?php
/**
 * Gravity Perks // Limit Submissions // Scope Feeds to the Current Nested Forms Parent Entry.
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 *
 * Set GP Limit Submissions feed to only apply to child entries submitted for the same parent entry.
 *
 * Plugin Name:  GP Limit Submissions — Scope Feeds to the Current Nested Forms Parent Entry.
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 * Description:  Set GP Limit Submissions feed to only apply to child entries submitted for the same parent entry.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_action( 'gpls_before_query', function( $ruletest ) {
	global $wpdb;

	// Update "123" to your child form ID.
	if ( $ruletest->form_id == 123 && class_exists( 'GPNF_Session' ) && $parent_form_id = rgpost( 'gpnf_parent_form_id' ) ) {
		$gpnf_session      = new GPNF_Session( $parent_form_id );
		$ruletest->join[]  = "INNER JOIN {$wpdb->prefix}gf_entry_meta em_gpnf ON em_gpnf.entry_id = e.id";
		$ruletest->where[] = sprintf( "\n( em_gpnf.meta_key = '%s' AND em_gpnf.meta_value = '%s' )", GPNF_Entry::ENTRY_PARENT_KEY, $gpnf_session->get( 'hash' ) );
	}

} );
