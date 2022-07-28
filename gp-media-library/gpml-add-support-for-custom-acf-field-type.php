<?php
/**
 * Gravity Perks // Media Library // Add Support For a Custom ACF Field Type
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'gpml_supported_acf_field_types', function( $supported_field_types ) {
	$supported_field_types[] = 'my_custom_acf_field_type';
	return $supported_field_types;
} );
