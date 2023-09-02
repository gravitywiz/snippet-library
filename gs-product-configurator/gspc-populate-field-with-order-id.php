<?php
/**
 * Gravity Shop // GS Product Configurator // Populate Gravity Forms field with WooCommerce Order ID
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Populate fields in a Gravity Form that is linked to a WooCommerce product using GS Product Configurator
 * with the WooCommerce order ID after checking out.
 *
 * Credit: Rochelle Victor (https://github.com/rochekaid)
 *
 * Instructions:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Add the CSS class "wc_gf_order_id" to any field in your Gravity Form that you want to populate with the
 *     WooCommerce order ID.
 */
add_action( 'woocommerce_checkout_order_processed', function( $order_id ) {
	if ( ! class_exists( '\GS_Product_Configurator\WC_Order_Item' ) || ! class_exists( 'GFAPI' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );

	/**
	 * @var string $field_css_class Any fields with this class will have its value replaced with the
	 *   WooCommerce order ID.
	 */
	$field_css_class = 'wc_gf_order_id';

	if ( ! $order ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		$gspc_order_item = \GS_Product_Configurator\WC_Order_Item::from( $item );

		foreach ( $gspc_order_item->get_entries() as $entry ) {
			$form          = GFAPI::get_form( $entry['form_id'] );
			$entry_updated = false;

			foreach ( $form['fields'] as $field ) {
				$css_classes = empty( $field->cssClass ) ? array() : explode( ' ', $field->cssClass );

				if ( ! in_array( $field_css_class, $css_classes, true ) ) {
					continue;
				}

				$entry[ $field->id ] = $order->ID;
				$entry_updated       = true;
			}

			if ( $entry_updated ) {
				GFAPI::update_entry( $entry );
			}
		}
	}
} );
