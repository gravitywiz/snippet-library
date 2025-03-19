<?php
/**
 * Gravity Shop // GS Product Configurator // Add order metadata to GF entries on export.
 *
 * Instructions:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Update the returned value in `gspc_add_order_meta_to_gf_entry_metas()` accordingly.
 */

if ( is_callable( 'gs_product_configurator' ) ) :

	function gspc_add_order_meta_to_gf_entry_metas() {
		/**
		 * Update this with the WC metadata keys you want to add. You
		 * may optionally include a label to use during export like so:
		 *     '_billing_phone' => 'Phone',
		 * If a label is not included, labels are generated thus:
		 *     e.g. 'Billing Phone' is used for '_billing_phone'.
		 *
		 * Please see class WC_Order_Data_Store_CPT in the WC plugin.
		 * You may also check your DB records or the extension files
		 * to see other order metadata keys that your installed WC
		 * extensions might be storing.
		 */
		return array(
			'_order_key',
			'_billing_first_name',
			'_billing_last_name',
			'_billing_email' => 'Email',
			'_billing_phone' => 'Phone',
			'_shipping_first_name',
			'_shipping_last_name',
			'_cart_discount',
			'_created_via',
		);
	}

	function gspc_add_order_meta_to_gf_entry_metas_keys() {
		$meta_keys = array();

		foreach ( gspc_add_order_meta_to_gf_entry_metas() as $key => $value ) {
			if ( is_int( $key ) ) {
				$meta_keys[] = $value;
			} else {
				$meta_keys[] = $key;
			}
		}

		return $meta_keys;
	}

	add_filter( 'gform_export_fields', function( $form ) {
		$metas = gspc_add_order_meta_to_gf_entry_metas();

		foreach ( $metas as $key => $value ) {
			if ( is_int( $key ) ) {
				$new_key   = $value;
				$new_value = trim( $value, " \n\r\t\v\x00_" );
				$new_value = preg_replace( '/[_]+/', ' ', $new_value );
				$new_value = ucwords( $new_value );
			} else {
				$new_key   = $key;
				$new_value = $value;
			}

			array_push(
				$form['fields'],
				array(
					'id'    => $new_key,
					'label' => $new_value,
				)
			);
		}

		return $form;
	}, 20 ); // we use 20 so that it comes after the one in GSPC core.

	add_filter( 'gform_export_field_value', function( $value, $form_id, $field_id, $entry ) {
		$meta_keys = gspc_add_order_meta_to_gf_entry_metas_keys();

		if ( ! in_array( $field_id, $meta_keys, true ) ) {
			return $value;
		}

		// custom field keys could clash, so confirm if this
		// entry is from a form embedded in a product page.
		$order_ids = gform_get_meta( $entry['id'], GS_Product_Configurator::ENTRY_WC_ORDER_IDS );
		$order_ids = maybe_unserialize( $order_ids );

		if ( ! is_array( $order_ids ) || ! count( $order_ids ) ) {
			return $value;
		}

		$orders = array_map( function( $id ) {
			$order = wc_get_order( $id );

			return $order ? $order : null;
		}, $order_ids );

		// There are situations in which an entry can be associated with multiple
		// order IDs (e.g. WooCommerce Subscriptions), so get the main order.
		$orders = array_filter( $orders, function( $order ) {
			return $order && ! $order->get_parent_id();
		} );
		$order  = $orders[0];

		$val = $order->get_meta( $field_id );
		$val = $val ? $val : '';

		if ( is_object( $val ) ) {
			$val = (array) $val;
		}

		if ( is_array( $val ) ) {
			array_walk( $val, function( &$v, $k ) {
				$v = "{$k} ({$v})";
			});
			$val = implode( ', ', $val );
		}

		$value = str_replace( array( "\n", "\t", "\r" ), ' ', $val );
		$value = htmlspecialchars_decode( $value );

		return $value;
	}, 10, 4 );

endif;
