<?php
/**
 * Gravity Perks // Nested Forms // User-specific Sessions
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Make Nested Forms sessions specific to the currently logged in user by appending the current User ID.
 */
add_filter( 'gpnf_cookie_name', function ( $name, $form_id ) {

	// Get the current User ID.
	$id = get_current_user_id();

	if ( $id ) {
		$name = sprintf( '%s_%s', $name, $id );
	}

	return $name;
}, 10, 2 );
