<?php
/**
 * Gravity Perks // Easy Passthrough // Prioritize Token over User Passthrough
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 */
add_filter( 'gpep_active_feeds', function( $feeds ) {

	if ( ! is_user_logged_in() || ! rgget( 'ep_token' ) ) {
		return $feeds;
	}

	foreach ( $feeds as &$feed ) {
		$feed['meta']['userPassthrough'] = false;
	}

	return $feeds;
} );
