<?php
/**
 * Gravity Perks // eCommerce Fields // Tax Amount by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * Instruction Video: https://www.loom.com/share/ca76b1f523f843e4b7d978a9c4877e61
 *
 * Set the tax amount of a Tax field based on the value of a field on a previous page.
 *
 * Plugin Name:  GP eCommerce Fields — Tax Amount by Field Value
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 * Description:  Set the tax amount of a Tax field based on the value of a field on a previous page.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   http://gravitywiz.com
 */
class GPECF_Tax_Amounts_By_Field_Value {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'             => false,
			'value_field_id'      => false,
			'tax_field_id'        => false,
			'tax_amounts'         => array(),
			'tax_amount_field_id' => false, // Optional dynamic tax source field
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'set_tax_amount_by_field_value' ) );
		add_filter( 'gform_pre_process', array( $this, 'set_tax_amount_by_field_value' ) );

		add_action( 'gform_product_info', array( $this, 'set_tax_amount_by_field_value_in_order' ), 8, 3 );

	}

	function set_tax_amount_by_field_value( $form ) {

		if ( ! $this->is_applicable_form( $form ) || $form['fields'][0]->is_form_editor() ) {
			return $form;
		}

		foreach ( $form['fields'] as $field ) {
			if ( $field->id == $this->_args['tax_field_id'] ) {

				$value = rgpost( sprintf(
					'input_%s',
					implode( '_', explode( '.', $this->_args['value_field_id'] ) )
				) );

				$field->taxAmount = $this->get_tax_amount_by_value( $value );
			}
		}

		return $form;
	}

	function set_tax_amount_by_field_value_in_order( $order, $form, $entry ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $order;
		}

		$tax_field = GFAPI::get_field( $form, $this->_args['tax_field_id'] );
		$value     = rgar( $entry, $this->_args['value_field_id'] );

		// Pass entry so dynamic field lookup works during submission
		$tax_field->taxAmount = $this->get_tax_amount_by_value( $value, $entry );

		return $order;
	}

	function get_tax_amount_by_value( $value, $entry = null ) {

		/**
		 * If a tax amount field ID is provided, use its value directly.
		 * This allows the tax amount to come from another field instead of
		 * the static tax_amounts configuration.
		 */
		if ( ! empty( $this->_args['tax_amount_field_id'] ) ) {

			// During submission we have entry data
			if ( $entry ) {
				$tax_amount = rgar( $entry, $this->_args['tax_amount_field_id'] );
			} else {
				$tax_amount = rgpost( sprintf(
					'input_%s',
					implode( '_', explode( '.', $this->_args['tax_amount_field_id'] ) )
				) );
			}

			return floatval( $tax_amount );
		}

		$tax_amount = rgar( $this->_args['tax_amounts'], $value, false );

		// Check for catch all amount if there is no tax amount for the given value.
		if ( $tax_amount === false ) {
			$tax_amount = rgar( $this->_args['tax_amounts'], '*', 0 );
		}

		return $tax_amount;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Configuration

// Option 1 — Static mapping
new GPECF_Tax_Amounts_By_Field_Value( array(
	'form_id'        => 123,
	'value_field_id' => 4,
	'tax_field_id'   => 5,
	'tax_amounts'    => array(
		'23325' => 10,
		'23462' => 25,
		// Provide a catch-all value.
		'*'     => 50,
	),
) );

// Option 2 — Pull tax amount dynamically from another field
new GPECF_Tax_Amounts_By_Field_Value( array(
	'form_id'             => 123,
	'tax_field_id'        => 5,
	'tax_amount_field_id' => 7,
) );
