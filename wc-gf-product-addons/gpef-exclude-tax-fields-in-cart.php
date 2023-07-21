<?php
/**
 * Gravity Perks // GP eCommerce Fields + Gravity Forms Product Add-ons for WooCommerce // Exclude Tax Fields
 * 
 * Note: See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/ for details on how to install snippets.
 *
 * Normally, GP eCommerce Fields Tax fields will be treated as Gravity Forms products when passed to WC Product Add-ons.
 * If WooCommerce is configured to handle taxes, passing the tax fields will result in double-taxation.
 *
 * This snippet prevents the tax fields/product from being added to WooCommerce cart.
 *
 * Use case: Display the tax on the product page to help show the customer a tax
 * estimate prior to getting to the cart.
 */
add_filter( 'gform_product_info', 'gwiz_exclude_tax_fields_from_wc_product_addons' );
function gwiz_exclude_tax_fields_from_wc_product_addons( $product_info ) {
	$products = $product_info['products'];

	if ( ! empty( $products ) && is_array( $products ) ) {
		$product_info['products'] = array_filter( $products, function ( $product ) {
			return ! rgar( $product, 'isTax' );
		} );
	}

	return $product_info;
}
