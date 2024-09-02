<?php
/**
 * Gravity Connect // Google Sheets // Process Feed when Partial Entry is Saved.
 *
 * Process Google Sheet feeds when a partial entry is saved.
 */
add_action( 'gform_partialentries_post_entry_saved', 'send_to_google_sheet_on_partial_entry_saved', 10, 2 );
function send_to_google_sheet_on_partial_entry_saved( $partial_entry, $form ) {
	if ( function_exists( 'gc_google_sheets' ) ) {
		$partial_entry['status'] = 'partial';
		gc_google_sheets()->maybe_process_feed( $partial_entry, $form );
		gf_feed_processor()->save()->dispatch();
	}
}
