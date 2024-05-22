<?php
/**
 * Gravity Shop // GS Product Configurator // Revalidate the cart for GP Limit Dates restrictions.
 *
 * Instructions:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */

use GS_Product_Configurator\WC_Cart_Item;

add_action( 'woocommerce_check_cart_items', function() {
	if ( ! is_callable( 'gs_product_configurator' ) ) {
		return;
	}

	$cart = WC()->cart->get_cart();

	foreach ( $cart as $cart_item_key => $values ) {
		$cart_item = WC_Cart_Item::from( $values );
		$entry_ids = $cart_item->get_entry_ids();
		$form      = $cart_item->get_form();

		if ( empty( $entry_ids ) || ! $form || ! $cart_item->get_entry() ) {
			continue;
		}

		foreach ( $form['fields'] as $field ) {
			if ( 'date' != $field->type ) {
				continue;
			}

			foreach ( $entry_ids as $entry_id ) {
				$date = gform_get_meta( $entry_id, $field->id );
				if ( ! $date ) {
					continue;
				}

				$result = array();
				if ( is_callable( 'gp_limit_dates' ) ) {
					$result = gp_limit_dates()->validate( $result, $date, $form, $field );
				} elseif ( strtotime( $date ) < strtotime( 'today' ) ) {
					$result['is_valid'] = false;
				}

				if ( $result && ! $result['is_valid'] ) {
					wc_add_notice(
						sprintf( 'Please enter a valid date for the <b>%s</b> field of the <b>%s</b> item.', $field->label, $values['data']->get_name() ),
						'error'
					);
				}
			}
		}
	}
}, 20 ); // we use 20 so that it comes after the ones in GSPC core.
