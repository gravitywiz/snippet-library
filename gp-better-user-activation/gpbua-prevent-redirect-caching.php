<?php
/**
 * Gravity Perks // Better User Activation // Prevent Redirect Caching
 * https://gravitywiz.com/documentation/gravity-forms-better-user-activation/
 *
 * Plugin Name:  Better User Activation - Prevent Redirect Caching
 * Plugin URI:   https://github.com/gravitywiz/snippet-library/blob/master/gp-better-user-activation/gpbua-prevent-redirect-caching.php
 * Description:  Adds a randomized query param to the redirect URL that Better User Activation uses.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gpbua_activation_redirect_url', function( $url, $activation ) {
	return add_query_arg( array(
		'rand' => rand( 100000, 999999 ),
	), $url );
}, 10, 2 );
