<?php
/**
 * Gravity Perks // Easy Passthrough // Disable Same Form Passthrough Without a Token
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * This example will prevent GPEP from continuously passing through entries 
 * if the ep_token is not present in the URL and the source and target forms are the same.
 */
add_filter('gpep_disable_same_form_passthrough', function( $disable ) {
	if ( ! rgget( 'ep_token' ) ) {
		$disable = true;
	}
	return $disable;
});
