<?php
/**
 * Gravity Perks // Auto Login // Redirect Automatically Logged-In Users to a Custom Page
 * https://gravitywiz.com/documentation/gravity-forms-auto-login/
 */
add_filter( 'gpal_auto_login_on_redirect_redirect_url', function( $redirect_url ) {
	return 'https://mydomain.com/custom_page';
} );
