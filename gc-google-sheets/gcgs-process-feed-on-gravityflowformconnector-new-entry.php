<?php
/**
 * Gravity Connect // Google Sheets // Process Feed when a New Entry is Created via Gravity Flow Form Connector.
 * 
 * Instruction Video: https://www.loom.com/share/7c5b3b5e286648538bf4a2326333f4d7
 *
 * Process Google Sheet feeds when a new entry is created.
 */
add_action( 'gravityflowformconnector_post_new_entry', function ( $new_entry_id, $entry, $form, $step ) {
	if ( function_exists( 'gc_google_sheets' ) && $step->get_type() == 'new_entry' ) {
		$new_entry = GFAPI::get_entry( $new_entry_id );
		$new_form  = GFAPI::get_form( $new_entry['form_id'] );
		gc_google_sheets()->maybe_process_feed( $new_entry, $new_form );
		gf_feed_processor()->save()->dispatch();
	}
}, 10, 4 );
