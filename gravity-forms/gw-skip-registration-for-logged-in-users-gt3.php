<?php
/**
 * Gravity Wiz // Gravity Forms // Skip Registration for Logged In Users
 * https://gravitywiz.com/skip-user-registration-for-logged-in-users/
 *
 * Skip registration if the user is logged in. Works for GF User Registration 3.0 and greater.
 *
 * Plugin Name: Gravity Forms - Skip Registration for Logged In Users
 * Plugin URI:  https://gravitywiz.com/skip-user-registration-for-logged-in-users/
 * Description: Skip registration if the user is logged in.
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: https://gravitywiz.com/
 */
add_filter( 'gform_is_delayed_pre_process_feed', 'maybe_delay_feeds', 10, 4 );
function maybe_delay_feeds( $is_delayed, $form, $entry, $slug ) {
	if ( is_user_logged_in() && $slug == 'gravityformsuserregistration' ) {
		return gf_user_registration()->has_feed_type( 'create', $form );
	}

	return $is_delayed;
}

add_action( 'gform_pre_process', 'maybe_skip_validation' );
function maybe_skip_validation( $form ) {
	if ( is_user_logged_in() && function_exists( 'gf_user_registration' ) && gf_user_registration()->has_feed_type( 'create', $form ) ) {
		remove_filter( 'gform_validation', array( gf_user_registration(), 'validate' ) );
	}
	return $form;
}
