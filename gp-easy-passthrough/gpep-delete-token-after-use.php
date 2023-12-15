<?php
/**
 * Gravity Perks // Easy Passthrough // Delete Token After Use
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * Delete the EP token that was used to populate a given form after that form is submitted.
 */
add_filter( 'gform_after_submission', function() {
	if ( rgget( 'ep_token' ) && is_callable( 'gp_easy_passthrough' ) ) {
		$entry = gp_easy_passthrough()->get_entry_for_token( rgget( 'ep_token' ) );
		if ( $entry ) {
			gform_delete_meta( $entry['id'], 'fg_easypassthrough_token' );
		}
	}
} );
