<?php
/**
 * Gravity Shop // GS Product Configurator // Allow switching subscriptions for products managed by GS Product Configurator
 *
 * By default, with WooCommerce Subscriptions, you can only switch subscriptions with products that are either
 * Variable Subscriptions or subscriptions in a Grouped Product.
 *
 * Instructions:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Add calls to gspc_allow_subscription_switching_for_product() for each product you want to allow switching for.
 */

// Example that allows switching for product ID 6 and changes the link text to "Modify Subscription"
// gspc_allow_subscription_switching_for_product( 6, 'Modify Subscription' );

function gspc_allow_subscription_switching_for_product( $product_id, $switch_link_text = null ) {
	add_filter( 'woocommerce_subscriptions_can_item_be_switched_by_user', function( $can_be_switched, $item ) use ( $product_id ) {
		if ( $item->get_product_id() != $product_id ) {
			return $can_be_switched;
		}

		return true;
	}, 10, 2 );

	add_filter( 'woocommerce_subscriptions_switch_is_identical_product', function ( $is_identical_product, $check_product_id ) use ( $product_id ) {
		if ( $check_product_id != $product_id ) {
			return $is_identical_product;
		}

		return false;
	}, 10, 2 );

	add_filter( 'woocommerce_subscriptions_switch_link_text', function ( $text, $item_id, $item ) use ( $product_id, $switch_link_text ) {
		if ( $item->get_product_id() != $product_id || empty( $switch_link_text ) ) {
			return $text;
		}

		return $switch_link_text;
	}, 10, 3 );
}
