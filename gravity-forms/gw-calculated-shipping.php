<?php
/**
 * Gravity Wiz // Gravity Forms // Calculated Shipping
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/aa10e3aceb4247528a27d1b27cc50516
 *
 * A simple method for using a Calculated Product field (or User Defined Price field) as a shipping field. This provides
 * the ability to use calculations when determining a shipping price.
 *
 * Note: This snippet does not work with GP eCommerce Fields.
 *
 * Plugin Name:  Gravity Forms - Calculated Shipping
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Use a calculated product field as a shipping field.
 * Author:       Gravity Wiz
 * Version:      1.2
 * Author URI:   https://gravitywiz.com/
 */
class GWCalculatedShipping {

	private $_orig_field = null;

	function __construct( $args ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		add_filter( 'gform_pre_process', array( $this, 'add_shipping_field' ), 9 );
		add_filter( 'gform_pre_render', array( $this, 'restore_original_field' ), 11 );

	}

	function add_shipping_field( $form ) {

		if ( $this->_args['form_id'] != $form['id'] ) {
			return $form;
		}

		// Trigger generation of admin version of product info that is typically only generated when accessing via Entry Detail
		// so it has our dynamically added Shipping field.
		add_filter( 'gform_product_info', array( $this, 'trigger_admin_product_info' ), 10, 3 );

		// get our calc shipping field and convert it to default shipping field
		// REMINDER: PHP objects are always passed by reference
		$field = GFFormsModel::get_field( $form, $this->_args['field_id'] );

		if ( $field->get_input_type() === 'calculation' ) {
			$shipping_value = rgpost( "input_{$field->id}_2" );
		} else {
			$shipping_value = rgpost( "input_{$field->id}" );
		}

		// create a copy of the original field so that it can be restored if there is a validation error
		$this->_orig_field = GF_Fields::create( $field );

		$field->type      = 'shipping';
		$field->inputType = 'singleshipping';
		$field->inputs    = null;

		$field = GF_Fields::create( $field );

		// map calc value as shipping value
		$_POST[ "input_{$field->id}" ] = $shipping_value;

		foreach ( $form['fields'] as &$_field ) {
			if ( $_field->id == $field->id ) {
				$_field = $field;
			}
		}

		return $form;
	}

	function trigger_admin_product_info( $product_info, $form, $entry ) {

		if ( $this->_args['form_id'] != $form['id'] ) {
			return $form;
		}

		remove_filter( 'gform_product_info', array( $this, 'trigger_admin_product_info' ) );

		GFCommon::get_product_fields( $form, $entry, false, true );

		return $product_info;
	}

	function field( $args = array() ) {
		return wp_parse_args( $args, array(
			'id'                 => false,
			'formId'             => false,
			'pageNumber'         => 1,
			'adminLabel'         => '',
			'adminOnly'          => '',
			'allowsPrepopulate'  => 1,
			'defaultValue'       => '',
			'description'        => '',
			'content'            => '',
			'cssClass'           => '',
			'errorMessage'       => '',
			'inputName'          => '',
			'isRequired'         => '',
			'label'              => 'Shipping',
			'noDuplicates'       => '',
			'size'               => 'medium',
			'type'               => 'shipping',
			'displayCaption'     => '',
			'displayDescription' => '',
			'displayTitle'       => '',
			'inputType'          => 'singleshipping',
			'inputs'             => '',
			'basePrice'          => '$0.00',
		) );
	}

	function restore_original_field( $form ) {

		if ( $this->_args['form_id'] != $form['id'] || empty( $this->_orig_field ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( $field['id'] == $this->_orig_field['id'] ) {
				$field = GF_Fields::create( $this->_orig_field );
				break;
			}
		}

		return $form;
	}

}

# Configuration

new GWCalculatedShipping( array(
	'form_id'  => 123,
	'field_id' => 4,
) );
