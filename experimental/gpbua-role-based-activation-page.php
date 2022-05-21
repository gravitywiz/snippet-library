<?php
/**
 * Gravity Perks // Better User Activation // Change Activation Page by User Role
 * https://gravitywiz.com/documentation/gravity-forms-better-user-activation/
 *
 * This experimental snippet allows you to load a different page (by ID) depending on the activated
 * user's role.
 */
add_filter( 'gpbua_activation_page_id', function( $activation_page_id ) {

	if ( ! class_exists( 'GFUserSignups' ) ) {
		return $activation_page_id;
	}

	$activate = new GPBUA_Activate();
	$activate->process_key();

	$signup = GFSignup::get( $activate->get_key() );
	if ( is_wp_error( $signup ) ) {
		if ( $signup->get_error_code() == 'already_active' ) {
			$user = new WP_User( 0, $signup->get_error_data( 'already_active' )->user_login );
		}
	}

	if ( $user ) {
		switch( $user->roles[0] ) {
			case 'contributor':
				$activation_page_id = 2742;
				break;
			case 'administrator':
				$activation_page_id = 963;
				break;
		}
	}

	return $activation_page_id;
} );
