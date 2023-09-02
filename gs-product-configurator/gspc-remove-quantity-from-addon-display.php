<?php
/**
 * Gravity Shop // GS Product Configurator // Remove Quantity from Addon Display
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 * *
 * Add "gspc-remove-quantity" to your field's CSS Class Name setting and this snippet will automatically remove the
 * quantity from the product's description in the cart and order details.
 *
 * This snippet expects the "Item Meta Display" feed setting to be "Default."
 *
 * Default description:
 * Product - Drop Down:
 * First Choice ($10.00) × 2
 *
 * Quantity removed:
 * Product - Drop Down:
 * First Choice ($10.00)
 */
add_filter( 'gspc_show_addon_quantity', function( $show, $product_field ) {
	if ( strpos( $field->cssClass, 'gspc-remove-quantity' ) !== false ) {
		return false;
	}

	return $show;
}, 10, 2 );
