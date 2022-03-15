<?php
/**
 * Gravity Perks // Easy Passthrough // Delete Passthrough Entry After Submission
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * Instruction Video: https://www.loom.com/share/e7f4f525a2d0438ea9cd45df959c87c9
 *
 * Delete the entry passed through via the EP token after the target form has been submitted.
 */
add_filter( 'gform_after_submission', function() {
	if ( rgget( 'ep_token' ) && is_callable( 'gp_easy_passthrough' ) ) {
		$entry = gp_easy_passthrough()->get_entry_for_token( rgget( 'ep_token' ) );
		if ( $entry ) {
			GFAPI::delete_entry( $entry['id'] );
		}
	}
} );
