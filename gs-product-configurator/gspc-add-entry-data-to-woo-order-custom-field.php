<?php
/**
 * Gravity Shop // GS Product Configurator // Add Entry Data to WooCommerce Order Custom Field
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Add data from the Gravity Forms entry attached to a cart item to a custom field on the WooCommerce order when the order is created.
 * This is useful for integrations such as ShipStation, which expect custom data to be attached to the order as a custom field.
 *
 * Instructions:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Configure the snippet based on inline instructions.
 */
 add_action( 'woocommerce_checkout_create_order_line_item', function ( $item, $cart_item_key, $values, $order ) {
    
	$entry_ids = $item->get_meta( 'gspc_gf_entry_ids', true );
		
	if( $entry_ids ){
		
		$entry_id = $entry_ids[0];
		
		$entry = GFAPI::get_entry( $entry_id );

		$data = rgar( $entry, '4' ); // Replace 4 with the field ID you need
		
		$order->update_meta_data( '_my_custom_key', $data ); // Replace _my_custom_key with your custom meta key
		
	}

}, 10, 4 );
