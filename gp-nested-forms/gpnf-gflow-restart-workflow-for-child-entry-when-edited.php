<?php
/**
 * Gravity Perks // Nested Forms // Restart Workflow for Child Entries when edited
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/0df02d719d4546fea88946a7eb3030cb
 */
add_filter( 'gform_entry_post_save', function ( $entry, $form ) {
	if ( rgar( $entry, 'gpnf_entry_parent' ) && gravity_flow()->is_workflow_detail_page() ) {
		$api = new Gravity_Flow_API( $form['id'] );
		$api->restart_workflow( $entry );
	}
	return $entry;
}, 10, 2 );
