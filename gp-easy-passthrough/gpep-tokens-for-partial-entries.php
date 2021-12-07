<?php
/**
 * Gravity Perks // Easy Passthrough // Generate Token for Partial Entries
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 */
add_action( 'gform_partialentries_post_entry_saved', function( $partial_entry ) {
	if ( is_callable( 'gp_easy_passthrough' ) ) {
		gp_easy_passthrough()->filter_gform_entry_post_save( $partial_entry );
	}
}, 9 );
