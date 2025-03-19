<?php
/**
 * Gravity Wiz // WooCommerce // Shortcode: Product Sales Count
 * https://gravitywiz.com/
 *
 * Return the total number of sales for a given product.
 *
 * Example: [wc_product_sales_count product_id="123"]
 */
add_shortcode( 'wc_product_sales_count', function( $atts ) {

	$atts = shortcode_atts(array(
		'product_id' => 0,
	), $atts);

	$product = wc_get_product( $atts['product_id'] );

	if ( $product ) {
		return $product->get_total_sales();
	} else {
		return 0;
	}

	return $order_count;
} );
