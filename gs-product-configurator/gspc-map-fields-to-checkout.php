<?php
/**
 * Gravity Shop // GS Product Configurator // Map Form Fields to WooCommerce Checkout
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Map Gravity Forms fields in the form attached to a product and automatically populate the WooCommerce checkout
 * accordingly. This opens up the possibility of auto-populating the WooCommerce billing and shipping details as well
 * as order comments.
 *
 * Instructions:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Configure the new "Checkout Field Map" section in the GS Product Configurator feed settings.
 */
add_filter('woocommerce_checkout_get_value', function( $input, $key ) {
	// Get current items in the cart.
	$cart_items = WC()->cart->get_cart();

	// Loop through cart items.
	foreach ( $cart_items as $cart_item ) {
		// Convert to a GSPC Cart Item
		$gspc_cart_item = \GS_Product_Configurator\WC_Cart_Item::from( $cart_item );

		// Get the feed for the cart item.
		$feed = $gspc_cart_item->get_feed();

		if ( ! $feed ) {
			continue;
		}

		// Get the entry for the cart item.
		$entry = $gspc_cart_item->get_entry();

		if ( ! $entry ) {
			continue;
		}

		// Map the fields to the checkout fields. The first cart item that has values is the one we use.
		$field_map = gs_product_configurator()->get_field_map_fields( $feed, 'checkoutFieldMap' );

		if ( empty( $field_map ) || ! is_array( $field_map ) ) {
			continue;
		}

		$values = array();

		foreach ( $field_map as $name => $input_id ) {
			if ( empty( $input_id ) || empty( $entry[ $input_id ] ) ) {
				continue;
			}

			// If the field is a country input in address field, convert it to abbreviation.
			$field      = GFAPI::get_field( $feed['form_id'], $input_id );
			$input_bits = explode( '.', $input_id );

			if ( $field && $field->type === 'address' && count( $input_bits ) == 2 && $input_bits[1] == 6 ) {
				$values[ $name ] = gspc_get_country_abbreviation( $entry[ $input_id ] );

				continue;
			}

			$values[ $name ] = $entry[ $input_id ];
		}

		if ( empty( array_filter( $values ) ) ) {
			continue;
		}

		foreach ( $values as $checkout_key => $value ) {
			if ( $checkout_key === $key ) {
				$input = $value;
				break;
			}
		}
	}

	return $input;
}, 10, 2);

function gspc_get_country_abbreviation( $country ) {
	$fake_address_field = new GF_Field_Address();

	if ( method_exists( $fake_address_field, 'get_default_countries' ) ) {
		/**
		 * @var array Associative array of countries. Keys are abbreviations for the country.
		 */
		$countries = $fake_address_field->get_default_countries();
	} else {
		$countries = array();
	}

	foreach ( $countries as $abbreviation => $country_name ) {
		if ( $country === $country_name || $country === $abbreviation ) {
			return $abbreviation;
		}
	}

	return '';
}

// Add a new section and field map to map the fields to the checkout fields.
add_filter( 'gform_gs-product-configurator_feed_settings_fields', function( $feed_settings_fields, $gspc ) {
	$feed_settings_fields[] = array(
		'title'  => esc_html__( 'Checkout Field Map', 'gs-product-configurator' ),
		'fields' => array(
			array(
				'name'        => 'checkoutFieldMap',
				'type'        => 'field_map',
				'key_field'   => array(
					'title' => 'Product Field',
				),
				'value_field' => array(
					'title' => 'Checkout Field',
				),
				'field_map'   => array(
					array(
						'name'       => 'billing_first_name',
						'label'      => esc_html__( 'Billing First Name', 'gs-product-configurator' ),
						'field_type' => array( 'text', 'name' ),
					),
					array(
						'name'       => 'billing_last_name',
						'label'      => esc_html__( 'Billing Last Name', 'gs-product-configurator' ),
						'field_type' => array( 'text', 'name' ),
					),
					array(
						'name'       => 'billing_company',
						'label'      => esc_html__( 'Billing Company', 'gs-product-configurator' ),
						'field_type' => array( 'text' ),
					),
					array(
						'name'       => 'billing_address_1',
						'label'      => esc_html__( 'Billing Address Line 1', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'billing_address_2',
						'label'      => esc_html__( 'Billing Address Line 2', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'billing_city',
						'label'      => esc_html__( 'Billing City', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'billing_state',
						'label'      => esc_html__( 'Billing State', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'billing_postcode',
						'label'      => esc_html__( 'Billing ZIP/Postal Code', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'billing_phone',
						'label'      => esc_html__( 'Billing Phone', 'gs-product-configurator' ),
						'field_type' => array( 'phone' ),
					),
					array(
						'name'       => 'billing_email',
						'label'      => esc_html__( 'Billing Email', 'gs-product-configurator' ),
						'field_type' => array( 'email' ),
					),
					array(
						'name'       => 'billing_country',
						'label'      => esc_html__( 'Billing Country', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'shipping_first_name',
						'label'      => esc_html__( 'Shipping First Name', 'gs-product-configurator' ),
						'field_type' => array( 'text', 'name' ),
					),
					array(
						'name'       => 'shipping_last_name',
						'label'      => esc_html__( 'Shipping Last Name', 'gs-product-configurator' ),
						'field_type' => array( 'text', 'name' ),
					),
					array(
						'name'       => 'shipping_company',
						'label'      => esc_html__( 'Shipping Company', 'gs-product-configurator' ),
						'field_type' => array( 'text' ),
					),
					array(
						'name'       => 'shipping_address_1',
						'label'      => esc_html__( 'Shipping Address Line 1', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'shipping_address_2',
						'label'      => esc_html__( 'Shipping Address Line 2', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'shipping_city',
						'label'      => esc_html__( 'Shipping City', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'shipping_state',
						'label'      => esc_html__( 'Shipping State', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'shipping_postcode',
						'label'      => esc_html__( 'Shipping ZIP/Postal Code', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'       => 'shipping_country',
						'label'      => esc_html__( 'Shipping Country', 'gs-product-configurator' ),
						'field_type' => array( 'address' ),
					),
					array(
						'name'  => 'order_comments',
						'label' => esc_html__( 'Order Comments', 'gs-product-configurator' ),
					),
				),
			),
		),
	);

	return $feed_settings_fields;
}, 10, 2 );
