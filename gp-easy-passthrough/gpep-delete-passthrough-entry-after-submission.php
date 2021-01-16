<?php
/**
 * Gravity Perks // Easy Passthrough // Delete Passthrough Entry After Submission
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * Delete the entry passed through via the EP token after the target form has been submitted.
 */
add_filter( 'gform_after_submission', function() {
	if ( rgget( 'ep_token' ) && is_callable( 'gp_easy_passthrough' ) ) {
		$entry = gp_easy_passthrough()->get_entry_for_token( rgget( 'ep_token' ) );
		GFAPI::delete_entry( $entry['id'] );
	}
} );
