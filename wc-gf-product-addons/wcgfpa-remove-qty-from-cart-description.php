<?php
/**
 * Gravity Forms // WC GF Product Add-ons // Remove Qty from Cart Description
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/00c1d1ec36bb4f1f93fa2d3f823ee264
 *
 * Add "wcgfpa-remove-qty" to your field's CSS Class Name setting and this snippet will automatically remove the
 * quantity from the product's description in the cart.
 *
 * Default description:
 * Product Name A: Qty: 1, Price: $0.00
 *
 * Quantity removed:
 * Product Name A: Price: $0.00
 */
add_filter( 'woocommerce_gforms_field_display_text', function( $display_text, $display_value, $field ) {
	if ( strpos( $field->cssClass, 'wcgfpa-remove-qty' ) !== false ) {
		preg_match_all( '/Qty: [0-9]*[.,]?[0-9]+, /', $display_text, $matches, PREG_SET_ORDER );
		$match        = array_pop( $matches );
		$display_text = str_replace( $match[0], '', $display_text );
	}
	return $display_text;
}, 10, 3 );
