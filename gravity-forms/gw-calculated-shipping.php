<?php
/**
 * Gravity Wiz // Gravity Forms // Calculated Shipping
 * https://gravitywiz.com/
 *
 * A simple method for using a calculated product field as a shipping field. This provides the ability to use
 * calculations when determining a shipping price.
 *
 * Plugin Name:  Gravity Forms - Calculated Shipping
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Use a calculated product field as a shipping field.
 * Author:       Gravity Wiz
 * Version:      1.1
 * Author URI:   https://gravitywiz.com/
 */
class GWCalculatedShipping {

	private $_orig_field = null;

	function __construct( $args ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		add_filter( 'gform_pre_validation', array( $this, 'add_shipping_field' ), 9 );
		add_filter( 'gform_pre_render', array( $this, 'restore_original_field' ), 11 );

	}

	function add_shipping_field( $form ) {

		if ( $this->_args['form_id'] != $form['id'] ) {
			return $form;
		}

		// get our calc shipping field and convert it to default shipping field
		// REMINDER: PHP objects are always passed by reference
		$field = GFFormsModel::get_field( $form, $this->_args['field_id'] );

		// create a copy of the original field so that it can be restored if there is a validation error
		$this->_orig_field = GF_Fields::create( $field );

		$field->type      = 'shipping';
		$field->inputType = 'singleshipping';
		$field->inputs    = null;

		$field = GF_Fields::create( $field );

		// map calc value as shipping value
		$_POST[ "input_{$field->id}" ] = rgpost( "input_{$field->id}_2" );

		foreach ( $form['fields'] as &$_field ) {
			if ( $_field->id == $field->id ) {
				$_field = $field;
			}
		}

		return $form;
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
	'form_id'  => 1378,
	'field_id' => 1,
) );
