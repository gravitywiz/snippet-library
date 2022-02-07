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
		} else {
			add_filter( 'gform_field_validation_' . $this->form_id, array( $this, 'allow_quantity_float' ), 10, 4 );
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
			$is_numeric = $field->type == 'product' ? GFCommon::is_numeric( rgpost( "input_{$field['id']}_3" ), 'decimal_dot' ) : GFCommon::is_numeric( rgpost( "input_{$field['id']}" ), 'decimal_dot' );
			if ( $is_numeric ) {
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
