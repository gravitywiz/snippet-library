<?php
/**
 * Gravity Wiz // Gravity Forms // Filter Out $0.00 Products
 * http://gravitywiz.com/
 *
 * Use this snippet to hide Product fields from the order summary when their cost is $0.00.
 *
 * Note: this snippet was originally designed to account for the lack of a true placeholder option
 * Gravity Forms Drop Down Products which resulted in the placeholder choice being added to the
 * order as a zero-cost line item. We are not aware of a current need for this snippet.
 */
add_filter( 'gform_product_info', 'gw_remove_empty_products', 10, 3 );
function gw_remove_empty_products( $product_info, $form, $lead ) {

	$products = array();

	foreach ( $product_info['products'] as $field_id => $product ) {
		if ( GFCommon::to_number( $product['price'] ) != 0 ) {
			$products[ $field_id ] = $product;
		}
	}

	$product_info['products'] = $products;

	return $product_info;
}
