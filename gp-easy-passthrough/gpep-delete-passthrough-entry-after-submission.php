<?php
/**
 * Gravity Perks // Easy Passthrough // Delete Passthrough Entry After Submission
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * Instruction Video: https://www.loom.com/share/91866484b97248d5bf6a2db576d11957
 *
 * Delete the entry passed through via the EP token after the target form has been submitted.
 *
 * **Note:** This differs from the [Delete Token After Use][1] snippet in that it fully deletes the passed through
 * entry rather than only deleting the token and preserving the entry.
 *
 * [1]: https://gravitywiz.com/snippet-library/gpep-delete-token-after-use/
 */
add_filter( 'gform_after_submission', function() {
	if ( rgget( 'ep_token' ) && is_callable( 'gp_easy_passthrough' ) ) {
		$entry = gp_easy_passthrough()->get_entry_for_token( rgget( 'ep_token' ) );
		if ( $entry ) {
			GFAPI::delete_entry( $entry['id'] );
		}
	}
} );
