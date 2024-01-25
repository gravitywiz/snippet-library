<?php
/**
 * Gravity Perks // Entry Blocks // Filter Block: Convert Checkboxes to Single Line Text
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Rather than displaying 50 checkboxes when using a Checkbox field in the Filter block, allow Entry Blocks
 * to convert the Checkbox field to a Single Line Text field in the Filter block context. Users can then
 * search for any checkbox value.
 */
add_filter( 'gpeb_filter_fields_preserve_type', function( $types ) {
	$types = array_diff( $types, array( 'checkbox' ) );
	return $types;
} );
