<?php
/**
 * Gravity Shop // GS Product Configurator // Remove Price from Addon Display
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Add "gspc-remove-price" to your field's CSS Class Name setting and this snippet will automatically remove the
 * price from the product's description in the cart.
 *
 * This snippet expects the "Item Meta Display" feed setting to be "Default."
 *
 * Default description:
 * Product - Drop Down:
 * First Choice ($10.00) × 2
 *
 * Price removed:
 * Product - Drop Down:
 * First Choice × 2
 */
add_filter( 'gspc_show_addon_price', function( $show, $product_field ) {
	if ( strpos( $product_field->cssClass, 'gspc-remove-price' ) !== false ) {
		return false;
	}

	return $show;
}, 10, 2 );
