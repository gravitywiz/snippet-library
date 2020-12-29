<?php
/**
 * Gravity Forms // WC GF Product Add-ons // Remove Price from Cart Description
 * https://gravitywiz.com/
 *
 * Add "wcgfpa-remove-price" to your field's CSS Class Name setting and this snippet will automatically remove the
 * price from the product's description in the cart.
 *
 * Default description:
 * Product Name B: Second Choice ($25.00)
 *
 * Price removed:
 * Product Name B: Second Choice
 */
add_filter( 'woocommerce_gforms_field_display_text', function( $display_text, $display_value, $field ) {
	if ( strpos( $field->cssClass, 'wcgfpa-remove-price' ) !== false ) {
		preg_match_all( '/\(.+?\)/', $display_text, $matches, PREG_SET_ORDER );
		$match = array_pop( $matches );
		$display_text = str_replace( $match[0], '', $display_text );
	}
	return $display_text;
}, 10, 3 );