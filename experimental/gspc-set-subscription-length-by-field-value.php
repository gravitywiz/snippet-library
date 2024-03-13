<?php
/**
 * Gravity Shop // Product Configurator // Set Subscription Length by Field Value
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 */
add_action( 'woocommerce_checkout_create_subscription', function ( $subscription ) {

	$form_field_map = array(
		// Update "123" to the ID of the form attached to the Subscription product and "4" to the field ID
		// that will determine the subscription length (in months).
		123 => 4,
		// Repeat that process for as many form/field pairs as you'd like.
		124 => 5,
	);

	$subscription_items = $subscription->get_items();

	foreach ( $subscription_items as $subscription_item ) {

		$gspc_order_item = new GS_Product_Configurator\WC_Order_Item( $subscription_item );
		$entries         = $gspc_order_item->get_entries();
		if ( empty( $entries ) ) {
			continue;
		}

		$form_id             = rgars( $entries, '0/form_id' );
		$sub_length_field_id = rgar( $form_field_map, $form_id );	
		if ( ! $sub_length_field_id ) {
			continue;
		}
		
		$sub_length = rgars( $entries, "0/{$sub_length_field_id}" );

		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$sub_end_date = date( 'Y-m-d H:i:s', strtotime( "+{$sub_length} months" ) );

		$subscription->update_dates( array( 'end' => $sub_end_date ) );

		return;

	}

} );
