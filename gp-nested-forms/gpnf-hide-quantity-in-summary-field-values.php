<?php
/**
 * Gravity Perks // Nested Forms // Hide Quantity in Summary Field Values
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Product field display values are formatted as "Label, Quantity, Price". This snippet removes the quantity from the display value.
 */
add_filter( 'gpnf_display_value', function( $display_value, $field, $form, $entry ) {

	if ( $field->type != 'product' ) {
		return $display_value;
	}

	$display_value['label'] = preg_replace( '/,\s*[^,]+,\s*/', ', ', $display_value['label']);

	return $display_value;
}, 10, 4 );
