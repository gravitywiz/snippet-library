<?php
/**
 * Gravity Perks // GP eCommerce Fields // Deduct Deposit from Order Summary
 * https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * Instruction Video: https://www.loom.com/share/303f0d636c964efcb89478ead9e5e3cb
 *
 * This snippet uses a Product field to create a deposit field and deducts the deposit from Order Summary.
 * To use the snippet, you'll have to update the Form ID and the deposit field ID within the snippet.
 *
 * Plugin Name:  GP eCommerce Fields - Deduct Deposit from Order Summary
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 * Description:  This snippet uses a Produdct field to create a deposit field and deducts the deposit from Order Summary
 * Author:       Gravity Wiz
 * Version:      0.3
 * Author URI:   https://gravitywiz.com
 */
class GW_Deduct_Deposit {

	private $_args = array();

	public function __construct( $args ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'          => false,
			'deposit_field_id' => false,
		) );

		// GPECF inits on priority 15 and we must wait for it to bind its function so we can remove it.
		add_action( 'init', array( $this, 'init' ), 16 );

	}

	public function init() {

		if ( ! class_exists( 'GFForms' ) || ! function_exists( 'gp_ecommerce_fields' ) ) {
			return;
		}

		remove_action( 'gform_product_info', array( gp_ecommerce_fields(), 'add_ecommerce_fields_to_order' ), 9 );

		add_action( 'gform_product_info', array( $this, 'deduct_deposit' ), 9, 3 );

	}

	public function deduct_deposit( $order, $form, $entry ) {

		// If we've already deducted deposits, return the order as is.
		if ( rgar( $order, 'depositsDeducted' ) ) {
			return $order;
			// Make sure we're processing this function only for the current instance of this class.
		} elseif ( (int) $form['id'] !== (int) $this->_args['form_id'] ) {
			return gp_ecommerce_fields()->add_ecommerce_fields_to_order( $order, $form, $entry );
		}

		$deposit =& $order['products'][ $this->_args['deposit_field_id'] ];

		// Run this first so calculations are reprocessed before we convert deposit to a negative number.
		$order = gp_ecommerce_fields()->add_ecommerce_fields_to_order( $order, $form, $entry );

		// Convert deposit to a negative number so it is deducted from the total.
		$deposit['price'] = GFCommon::to_money( GFCommon::to_number( $deposit['price'], $entry['currency'] ) * $deposit['quantity'] * - 1, $entry['currency'] );

		// Quantity is factored into price above.
		$deposit['quantity'] = 1;

		// Set the discount flag so GP eCommerce Fields knows this is a deposit.
		$deposit['isDiscount'] = true;

		// Indicate that this order has been processed for deposits.
		$order['depositsDeducted'] = true;

		return $order;

	}

}

# Configuration

new GW_Deduct_Deposit( array(
	'form_id'          => 123, // Update "123" to the ID of your form.
	'deposit_field_id' => 4,   // Update the "4" to your deposit field ID.
) );
