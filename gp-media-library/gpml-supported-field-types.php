<?php
/**
 * Gravity Perks // GP Media Library // Filter Supported Field Types
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'gpml_supported_field_types', function( $supported_field_types ) {

	$supported_field_types[] = 'example_field_type';

	return $supported_field_types;
} );
