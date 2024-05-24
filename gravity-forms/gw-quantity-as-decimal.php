<?php
/**
 * Gravity Wiz // Gravity Forms // Accept Decimal Values for Quantity Fields
 * https://gravitywiz.com/enable-decimal-values-in-quantity-fields/
 *
 * Allows you to accept decimal values in Quantity fields, converting any Quantity field into a weight field.
 *
 * Plugin Name:  Gravity Forms - Accept Decimal Values for Quantity Fields
 * Plugin URI:   https://gravitywiz.com/enable-decimal-values-in-quantity-fields/
 * Description:  Allows you to accept decimal values in Quantity fields.
 * Author:       Gravity Wiz
 * Version:      1.3
 * Author URI:   https://gravitywiz.com/
 */
class GW_Quantity_Decimal {

	private static $_current_form;
	private $form_id;
	private $global    = false;
	private $field_ids = array();

	function __construct( $form_id, $field_ids = array(), $global = false ) {

		if ( ! is_array( $field_ids ) ) {
			$field_ids = array( $field_ids );
		}

		$this->form_id   = ( ! $global ) ? $form_id : null;
		$this->field_ids = $field_ids;
		$this->global    = $global;

		add_action( 'init', array( $this, 'init' ) );

	}

	function init() {

		// make sure Gravity Forms is loaded
		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		if ( $this->global ) {
			add_filter( 'gform_field_validation', array( $this, 'allow_quantity_float' ), 10, 4 );
			add_filter( 'gform_submission_data_pre_process_payment', array( $this, 'modify_submission_data' ), 10, 4 );
		} else {
			add_filter( 'gform_field_validation_' . $this->form_id, array( $this, 'allow_quantity_float' ), 10, 4 );
			add_filter( 'gform_submission_data_pre_process_payment_' . $this->form_id, array( $this, 'modify_submission_data' ), 10, 4 );

		}

		if ( GFFormsModel::is_html5_enabled() ) {
			add_filter( 'gform_pre_render', array( $this, 'stash_current_form' ) );
			add_filter( 'gform_field_input', array( $this, 'modify_quantity_input_tag' ), 10, 5 );
		}

	}

	function allow_quantity_float( $result, $value, $form, $field ) {
		if (
			$this->is_enabled_field( $field ) &&
			in_array( $field->type, array( 'product', 'quantity' ) ) &&
			in_array( $field->validation_message, array( __( 'Please enter a valid quantity. Quantity cannot contain decimals.', 'gravityforms' ), __( 'Please enter a valid quantity', 'gravityforms' ) ) ) ) {
			$is_numeric_decimal_dot   = $field->type == 'product' ? GFCommon::is_numeric( rgpost( "input_{$field['id']}_3" ), 'decimal_dot' ) : GFCommon::is_numeric( rgpost( "input_{$field['id']}" ), 'decimal_dot' );
			$is_numeric_decimal_comma = $field->type == 'product' ? GFCommon::is_numeric( rgpost( "input_{$field['id']}_3" ), 'decimal_comma' ) : GFCommon::is_numeric( rgpost( "input_{$field['id']}" ), 'decimal_comma' );
			if ( $is_numeric_decimal_dot || $is_numeric_decimal_comma ) {
				$result['is_valid'] = true;
			}
		}
		return $result;
	}

	function stash_current_form( $form ) {
		self::$_current_form = $form;
		return $form;
	}

	function modify_quantity_input_tag( $markup, $field, $value, $lead_id, $form_id ) {

		$is_correct_form         = $this->form_id == $form_id || $this->global;
		$is_correct_stashed_form = self::$_current_form && self::$_current_form['id'] == $form_id;

		if ( ! $is_correct_form || ! $is_correct_stashed_form || ! $this->is_enabled_field( $field ) ) {
			return $markup;
		}

		$markup = $this->get_field_input( $field, $value, self::$_current_form );

		$search  = 'type=\'number\'';
		$replace = $search . ' step=\'any\'';
		$markup  = str_replace( $search, $replace, $markup );

		return $markup;
	}

	function modify_submission_data( $submission_data, $feed, $form, $entry ) {

		$products = GFCommon::get_product_fields( $form, $entry );

		$key             = rgars( $feed, 'meta/transactionType' ) === 'subscription' ? 'recurringAmount' : 'paymentAmount';
		$payment_field   = rgars( $feed, 'meta/' . $key, 'form_total' );
		$setup_fee_field = rgar( $feed['meta'], 'setupFee_enabled' ) ? $feed['meta']['setupFee_product'] : false;
		$trial_field     = rgar( $feed['meta'], 'trial_enabled' ) ? rgars( $feed, 'meta/trial_product' ) : false;

		$amount       = 0;
		$line_items   = array();
		$discounts    = array();
		$fee_amount   = 0;
		$trial_amount = 0;
		foreach ( $products['products'] as $field_id => $product ) {

			$quantity      = isset( $product['quantity'] ) ? $product['quantity'] : 1;
			$product_price = GFCommon::to_number( $product['price'], $entry['currency'] );

			$options = array();
			if ( is_array( rgar( $product, 'options' ) ) ) {
				foreach ( $product['options'] as $option ) {
					$options[]      = $option['option_name'];
					$product_price += $option['price'];
				}
			}

			$is_trial_or_setup_fee = false;

			if ( ! empty( $trial_field ) && $trial_field == $field_id ) {

				$trial_amount          = $product_price * $quantity;
				$is_trial_or_setup_fee = true;

			} elseif ( ! empty( $setup_fee_field ) && $setup_fee_field == $field_id ) {

				$fee_amount            = $product_price * $quantity;
				$is_trial_or_setup_fee = true;
			}

			// Do not add to line items if the payment field selected in the feed is not the current field.
			if ( is_numeric( $payment_field ) && $payment_field != $field_id ) {
				continue;
			}

			// Do not add to line items if the payment field is set to "Form Total" and the current field was used for trial or setup fee.
			if ( $is_trial_or_setup_fee && ! is_numeric( $payment_field ) ) {
				continue;
			}

			$amount += $product_price * $quantity;

			$description = '';
			if ( ! empty( $options ) ) {
				$description = esc_html__( 'options: ', 'gravityforms' ) . ' ' . implode( ', ', $options );
			}

			if ( $product_price >= 0 ) {
				$line_items[] = array(
					'id'          => $field_id,
					'name'        => $product['name'],
					'description' => $description,
					'quantity'    => $quantity,
					'unit_price'  => GFCommon::to_number( $product_price, $entry['currency'] ),
					'options'     => rgar( $product, 'options' ),
				);
			} else {
				$discounts[] = array(
					'id'          => $field_id,
					'name'        => $product['name'],
					'description' => $description,
					'quantity'    => $quantity,
					'unit_price'  => GFCommon::to_number( $product_price, $entry['currency'] ),
					'options'     => rgar( $product, 'options' ),
				);
			}
		}

		if ( $trial_field == 'enter_amount' ) {
			$trial_amount = rgar( $feed['meta'], 'trial_amount' ) ? GFCommon::to_number( rgar( $feed['meta'], 'trial_amount' ), $entry['currency'] ) : 0;
		}

		if ( ! empty( $products['shipping']['name'] ) && ! is_numeric( $payment_field ) ) {
			$line_items[] = array(
				'id'          => $products['shipping']['id'],
				'name'        => $products['shipping']['name'],
				'description' => '',
				'quantity'    => 1,
				'unit_price'  => GFCommon::to_number( $products['shipping']['price'], $entry['currency'] ),
				'is_shipping' => 1,
			);
			$amount      += $products['shipping']['price'];
		}

		// Round amount to resolve floating point precision issues.
		$currency = RGCurrency::get_currency( $entry['currency'] );
		$decimals = rgar( $currency, 'decimals', 0 );
		$amount   = GFCommon::round_number( $amount, $decimals );

		$submission_data['payment_amount'] = $amount;
		$submission_data['setup_fee']      = $fee_amount;
		$submission_data['trial']          = $trial_amount;
		$submission_data['line_items']     = $line_items;
		$submission_data['discounts']      = $discounts;

		return $submission_data;
	}

	function get_field_input( $field, $value, $form ) {

		remove_filter( 'gform_field_input', array( $this, 'modify_quantity_input_tag' ), 10, 5 );

		$input = GFCommon::get_field_input( $field, $value, 0, $form['id'], $form );

		add_filter( 'gform_field_input', array( $this, 'modify_quantity_input_tag' ), 10, 5 );

		return $input;
	}

	function is_enabled_field( $field ) {
		return is_array( $this->field_ids ) && ! empty( $this->field_ids ) ? in_array( $field['id'], $this->field_ids ) : true;
	}

}
// Global sub-class
class GW_Quantity_Decimal_Global extends GW_Quantity_Decimal {
	function __construct( $form_id = null, $field_ids = array() ) {
		parent::__construct( $form_id, $field_ids, true );
	}
}

# accept quantity as decimal for any fields
new GW_Quantity_Decimal( 123 );

# accept quantity as decimal for a single field
// new GW_Quantity_Decimal( 123, 1 );

# accept quantity as decimal for a group of fields
// new GW_Quantity_Decimal( 123, array( 1, 2, 3 ) );

# accept quantity as decimal for any fields in ALL forms
// new GW_Quantity_Decimal_Global();

# accept quantity as decimal for a single field in ALL forms (field ID must match globally)
// new GW_Quantity_Decimal_Global( null, 1 );

# accept quantity as decimal for a group of fields
// new GW_Quantity_Decimal_Global( null, array( 1, 2, 3 ) );
