<?php
/**
 * Gravity Perks // Nested Forms // Include Posts of a Custom Post Type in the Embed URL › Post/Page Rule
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gpls_rule_get_post_args', function( $args ) {
	// Change "my_custom_post_type" to the slug of your custom post type.
	$args['post_type'][] = 'my_custom_post_type';
	return $args;
} );
