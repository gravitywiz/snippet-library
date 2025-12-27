<?php
/**
 * Gravity Shop // GS Product Configurator // Hide Woo Price For All GSPC Products
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Hide the price for all GSPC products in the shop, archive and single product pages.
 *
 * Instructions:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'woocommerce_get_price_html', function( $price, $product ) {
	if ( ! is_admin() && gspc_get_product_form_id( $product ) ) {
		return '';
	}
	return $price;
}, 10, 2 );
