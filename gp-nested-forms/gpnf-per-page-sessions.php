<?php
/**
 * Gravity Perks // Nested Forms // Create Unique Sessions Per Page
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * By default, Nested Forms creates a session that is specific to the parent form. If you're 
 * embedding the parent form on multiple pages, it may be undesirable for the parent form to share 
 * the same session. This snippet will make the session unique to the page the parent form is 
 * embedded on.
 */
add_filter( 'gpnf_cookie_name', function ( $name ) {
	$url  = defined( 'DOING_AJAX' ) && DOING_AJAX ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI'];
	$bits = parse_url( $url );
	$mod  = $bits['path'] . '?' . $bits['query'];
	return $name . md5( $mod );
} );
