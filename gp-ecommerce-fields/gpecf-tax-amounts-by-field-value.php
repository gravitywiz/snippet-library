<?php
/**
 * Gravity Perks // eCommerce Fields // Tax Amount by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * Set the tax amount of a Tax field based on the value of a field on a previous page.
 *
 * Plugin Name:  GP eCommerce Fields — Tax Amount by Field Value
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 * Description:  Set the tax amount of a Tax field based on the value of a field on a previous page.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GPECF_Tax_Amounts_By_Field_Value {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'        => false,
			'value_field_id' => false,
			'tax_field_id'   => false,
			'tax_amounts'    => array()
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
				$value            = rgpost( sprintf( 'input_%s', implode( '_', explode( '.', $this->_args['value_field_id'] ) ) ) );
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

		$tax_field->taxAmount = $this->get_tax_amount_by_value( $value );

		return $order;
	}

	function get_tax_amount_by_value( $value ) {
		return rgar( $this->_args['tax_amounts'], $value, 0 );
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Configuration

new GPECF_Tax_Amounts_By_Field_Value( array(
	'form_id'        => 123,
	'value_field_id' => 4,
	'tax_field_id'   => 5,
	'tax_amounts'    => array(
		'23325' => 10,
		'23462' => 25,
	),
) );
