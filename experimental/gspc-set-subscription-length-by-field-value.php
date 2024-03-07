<?php
/**
 * Gravity Shop // Product Configurator // Set Subscription Length by Field Value
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 */
add_action( 'woocommerce_checkout_create_subscription', function ( $subscription ) {

	// Update "123" to the ID of the form attached to the Subscription product.
	$target_form_id = 123;

	// Update "4" to the field ID that will determine the subscription length (in months).
	$sub_length_field_id = 4;

	$subscription_items = $subscription->get_items();

	foreach ( $subscription_items as $subscription_item ) {

		$gspc_order_item = new GS_Product_Configurator\WC_Order_Item( $subscription_item );
		$entries         = $gspc_order_item->get_entries();
		if ( empty( $entries ) || rgars( $entries, '0/form_id' ) != $target_form_id ) {
			continue;
		}

		$sub_length = 3; // rgars( $entries, "0/{$sub_length_field_id}" );

		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$sub_end_date = date( 'Y-m-d H:i:s', strtotime( "+{$sub_length} months" ) );

		$subscription->update_dates( array( 'end' => $sub_end_date ) );

		return;

	}

} );
