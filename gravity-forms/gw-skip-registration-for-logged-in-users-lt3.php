<?php
/**
 * Gravity Wiz // Gravity Forms // Skip Registration for Logged In Users
 * https://gravitywiz.com/skip-user-registration-for-logged-in-users/
 *
 * Skip registration if the user is logged in. Works for GF User Registration versions before 3.0.
 * For version 3.0 and greater, use this alternate snippet: <URL to be added after Merging PR>
 *
 * Plugin Name: Gravity Forms - Skip Registration for Logged In Users
 * Plugin URI:  https://gravitywiz.com/skip-user-registration-for-logged-in-users/
 * Description: Skip registration if the user is logged in.
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: https://gravitywiz.com/
 */
add_action( 'gform_post_submission', 'maybe_skip_registration', 9 );
add_action( 'gform_after_submission', 'maybe_skip_registration', 9 );
function maybe_skip_registration( $entry ) {

	if ( is_user_logged_in() ) {
		remove_action( 'gform_post_submission', array( 'GFUser', 'gf_create_user' ) );
		remove_action( 'gform_after_submission', array( 'GFUser', 'gf_create_user' ) );
	}

}

add_filter( 'gform_validation', 'maybe_skip_validation', 9 );
function maybe_skip_validation( $validation_result ) {
	if ( is_user_logged_in() ) {
		remove_filter( 'gform_validation', array( 'GFUser', 'user_registration_validation' ) );
	}
	return $validation_result;
}
