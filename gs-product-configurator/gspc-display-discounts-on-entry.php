<?php
/**
 * Gravity Shop // Product Configurator // Show WooCommerce Discounts on Entry
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * If a discount is applied at checkout, it will be displayed in the WooCommerce order summary. By default, this
 * discount is not displayed on the associated Gravity Forms entry (via GSPC). This snippet will display cart item
 * discounts as a line item in the entry order summary as well.
 */
add_filter( 'gform_product_info', function( $product_info, $form, $entry ) {

	$order_item_id = gform_get_meta( $entry['id'], GS_Product_Configurator::ENTRY_WC_ORDER_ITEM_ID );
	$order_item    = \WC_Order_Factory::get_order_item( $order_item_id );
	if ( ! $order_item ) {
		return $product_info;
	}

	$order = $order_item->get_order();

	if ( $order_item->get_subtotal() !== $order_item->get_total() ) {
		$product_info['products'][ \GS_Product_Configurator\WC_Product_Form_Display::BASE_PRICE_PRODUCT_FIELD_ID . '.discount' ] = [
			'name'     => __( 'Discount' ),
			'price'    => wc_price( wc_format_decimal( ( $order_item->get_subtotal() - $order_item->get_total() ) * -1, '' ), array( 'currency' => $order->get_currency() ) ),
			'quantity' => 1,
		];
	}

	return $product_info;
}, 10, 3 );
