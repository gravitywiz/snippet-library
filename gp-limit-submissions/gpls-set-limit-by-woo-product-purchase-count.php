<?php
/**
 * Gravity Perks // Limit Submissions // Dynamically Set Limit by User WooCommerce Product Purchase Count
 * https://gravitywiz.com/documentaiton/gravity-forms-limit-submissions/
 *
 * Dynamically set the limit by how many times the current user has purchased a specific WooCommerce product.
 * This is useful when a user must complete a form once each time they purchase the product.
 * Note: This requires users to be logged in, so does not work for guest purchases.
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/
 *
 * 2. Configure the snippet based on inline instructions.
 */
// Update "123" to your form ID.
add_filter( 'gpls_rule_groups_123', function ( $rule_groups ) {

	$target_product_id = 4; // Update "4" to your Woo product ID

	$current_user_id = get_current_user_id();
	if ( ! $current_user_id ) {
		$rule_groups[0]->limit = 0;
		return $rule_groups;
	}

	$customer_orders = wc_get_orders(
		array(
			'limit'    => -1,
			'status'   => array( 'completed', 'processing' ),
			'customer' => $current_user_id,
		)
	);

	$purchase_count = 0;
	foreach ( $customer_orders as $customer_order ) {
		$order       = wc_get_order( $customer_order->get_id() );
		$order_items = $order->get_items();
		foreach ( $order_items as $item ) {
			$product_id = $item->get_product_id();
			if ( (int) $item->get_product_id() === (int) $target_product_id ) {
				$purchase_count += absint( $item->get_quantity() );
			}
		}
	}

	$rule_groups[0]->limit = (int) $purchase_count;
	return $rule_groups;
} );
