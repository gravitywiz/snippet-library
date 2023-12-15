<?php
/**
 * Gravity Perks // Populate Anything // Add Live Merge Tag to Whitelist
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_lmt_whitelist', function( $whitelist, $form ) {
	$merge_tag = '{ip}';

	$whitelist[ $merge_tag ] = wp_create_nonce( 'gppa-lmt-' . $form['id'] . '-' . $merge_tag );

	return $whitelist;
}, 10, 2 );
