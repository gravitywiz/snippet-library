<?php
/**
 * Gravity Perks // GP Live Preview // Redirect unauthenticated users to the login page.
 * https://gravitywiz.com/documentation/gravity-forms-live-preview/
 *
 * By default, Live Preview will show a login form if a user is not logged in or does
 * not have the permissions to preview a form.
 *
 * In some situations, these pages can be indexed by search engines. To remedy this,
 * this snippet redirects to `wp-login.php` instead of showing a login form.
 *
 * Installation:
 *  1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_action( 'wp', function() {
	if ( ! function_exists( 'gp_live_preview' ) ) {
		return;
	}

	if ( GFCommon::current_user_can_any( 'gravityforms_preview_forms' ) ) {
		return;
	}

	if ( ! gp_live_preview()->is_live_preview() ) {
		return;
	}

	$current_url = 'http' . ( is_ssl() ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	wp_safe_redirect( wp_login_url( $current_url ) );

	die();
} );
