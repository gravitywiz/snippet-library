<?php
/**
 * Gravity Shop // Product Configurator // Map Order Data to Entry
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Populate fields that are linked to a WooCommerce product using GS Product Configurator with values from the WooCommerce order.
 *
 * Instructions Video: https://www.loom.com/share/423b1e3835dc4757aae26a3efe9351b0
 *
 * Plugin Name:  GSPC Map Order Data to Entry
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 * Description:  Populate fields that are linked to a WooCommerce product using GS Product Configurator with values from the WooCommerce order.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GSPC_Map_Order_Data_to_Entry {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'   => false,
			'field_map' => array(),
		) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		if ( ! property_exists( 'GFCommon', 'version' ) || 
		     ! class_exists( '\GS_Product_Configurator\WC_Order_Item' ) || 
		     ! class_exists( 'GFAPI' ) ) {
			return;
		}

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'map_order_data_to_entries' ), 10, 3 );
	}

	public function map_order_data_to_entries( $order_id, $posted_data, $order ) {

		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {
			$gspc_order_item = \GS_Product_Configurator\WC_Order_Item::from( $item );

			foreach ( $gspc_order_item->get_entries() as $entry ) {

				if ( $this->_args['form_id'] && $this->_args['form_id'] != $entry['form_id'] ) {
					continue;
				}

				$form = GFAPI::get_form( $entry['form_id'] );
				$entry_updated = false;

				foreach ( $this->_args['field_map'] as $field_key => $data_key ) {

					$field_id = strpos( $field_key, '.' ) !== false ? 
						explode( '.', $field_key )[0] : 
						$field_key;

					$field = GFFormsModel::get_field( $form, $field_id );
					if ( ! $field ) {
						continue;
					}

					$value = $this->get_value_from_order( $order, $data_key );
					if ( $value !== null ) {
						$entry[ $field_key ] = $value;
						$entry_updated = true;
					}
				}

				if ( $entry_updated ) {
					GFAPI::update_entry( $entry );
				}
			}
		}
	}

	private function get_value_from_order( $order, $data_key ) {
		$order_data = $order->get_data();


		switch ( $data_key ) {
			case 'id':
				return $order->get_id();
			case 'email':
				return $order->get_billing_email();
			case 'status':
				return $order->get_status();
			case 'total':
				return $order->get_total();
		}

		// Nested data (e.g., billing/first_name)
		$parts = explode( '/', $data_key );
		$current = $order_data;

		foreach ( $parts as $part ) {
			if ( isset( $current[ $part ] ) ) {
				$current = $current[ $part ];
			} else {
				return null;
			}
		}

		return $current;
	}
}

# Configuration

new GSPC_Map_Order_Data_to_Entry( array(
	'form_id'    => 123,                 // Replace with your form ID
	'field_map'  => array(
		'2'     => 'id',                 // Field ID 2 will store the order ID
		'3.3'   => 'billing/first_name', // Field ID 3, input 3 (first name)
		'3.6'   => 'billing/last_name',  // Field ID 3, input 6 (last name)
		'4'     => 'email',              // Field ID 4 will store the email
		'5'     => 'total',              // Field ID 5 will store the order total
	),
) );