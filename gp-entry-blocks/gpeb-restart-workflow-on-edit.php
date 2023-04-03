<?php
/**
 * Gravity Perks // Entry Blocks // Restart Workflow on Entry Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 */
// Update "123" with your form ID.
add_filter( 'gform_entry_created_123', function( $entry, $form ) {
	if ( class_exists( 'Gravity_Flow_API' )
	     && is_callable( 'gp_entry_blocks' )
	     && gp_entry_blocks()->block_edit_form->has_submitted_edited_entry()
	) {
		$api = new Gravity_Flow_API( $form['id'] );
		$api->restart_workflow( $entry );
	}
}, 10, 2 );
