<?php
/**
 * Gravity Shop // Product Configurator // Remove URL for Hidden Products in Cart.
 * https://gravitywiz.com/documentation/gs-product-configurator/
 */
add_filter( 'woocommerce_cart_item_name', function ( $product_name, $cart_item, $cart_item_key ) {
	$product = $cart_item['data'];
	if ( ! ( $product instanceof WC_Product ) ) {
		return $product_name;
	}

	// Remove the link for hidden products
	if ( $product->get_catalog_visibility() === 'hidden' ) {
		$product_name = strip_tags( $product_name );
	}

	return $product_name;
}, 10, 3 );
