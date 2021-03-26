<?php
/**
 * Gravity Perks // GP eCommerce Fields // Deduct Deposit from Order Summary
 * http://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 */
add_action( 'wp_loaded', function() {

	if( ! function_exists( 'gp_ecommerce_fields' ) ) {
		return;
	}

	remove_action( 'gform_product_info', array( gp_ecommerce_fields(), 'add_ecommerce_fields_to_order' ), 9, 3 );
	add_action( 'gform_product_info', function( $order, $form, $entry ) {

		// CHANGE: Update "123" to the ID of your form.
		if( $form['id'] != 123 ) {
			return gp_ecommerce_fields()->add_ecommerce_fields_to_order( $order, $form, $entry );
		}

		// CHANGE: Update the "2" to your deposit field ID.
		$deposit =& $order['products'][2];

		// Run this first so calculations are reprocessed before we convert deposit to a negative number.
		$order = gp_ecommerce_fields()->add_ecommerce_fields_to_order( $order, $form, $entry );

		// Convert deposit to a negative number so it is deducted from the total.
		$deposit['price'] = GFCommon::to_money( GFCommon::to_number( $deposit['price'], $entry['currency'] ) * $deposit['quantity'] * -1, $entry['currency'] );

		// Quantity is factored into price above.
		$deposit['quantity'] = 1;

		// Set the discount flag so GP eCommerce Fields knows this is a deposit.
		$deposit['isDiscount'] = true;

		return $order;
	}, 9, 3 );

} );
