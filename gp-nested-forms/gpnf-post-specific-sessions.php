<?php
/**
 * Gravity Perks // Nested Forms // Post-specific Sessions
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Make Nested Forms sessions specific to the post on which the form is rendered by appending the current post ID.
 */
add_filter( 'gpnf_cookie_name', function ( $name, $form_id ) {

	// Get the current post ID.
	$id = get_the_ID();

	// Check referrer for the ID in AJAX calls.
	$id = $id ? $id : url_to_postid( $_SERVER['HTTP_REFERER'] );

	if ( $id ) {
		$name = sprintf( '%s_%s', $name, $id );
	}

	return $name;
}, 10, 2 );
