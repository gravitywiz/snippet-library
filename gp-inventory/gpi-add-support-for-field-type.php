<?php
/**
 * Gravity Perks // Inventory // Add Number as a Supported Field Type
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
add_filter( 'gpi_supported_field_types', function( $field_types ) {
	// Update 'number' to desired field type.
	$field_types['number'] = true;

	return $field_types;
} );
