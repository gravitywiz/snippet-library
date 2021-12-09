<?php
/**
 * Gravity Perks // Nested Forms // Exclude Gravity Forms Inline Scripts from CloudFlare's Rocket Loader™.
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'wp_inline_script_attributes', function ( $attributes, $javascript ) {
	if ( strpos( $javascript, 'gform' ) ) {
		$attributes['data-cfasync'] = 'false';	
	}
	return $attributes;
}, 10, 2 );
