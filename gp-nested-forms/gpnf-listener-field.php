<?php
/**
 * Gravity Perks // Nested Forms // Nested Form Field Listener
 *
 * Add a form field that will listen for changes in a Nested Form field and update to pull the value of a designated
 * child form field.
 *
 * # Current Limitations
 *
 * 1. The value will only be retrieved from the last submitted child entry in the designed Nested Form field.
 * 2. The Listener field's value will only update on the first submission.
 *
 * @version  0.1
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Plugin Name:  GPNF Listener Field
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Add a form field that will listen for changes in a Nested Form field and update to pull the value of a designated child form field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GPNF_Listener_Field {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'              => false,
			'nested_form_field_id' => false,
			'target_field_id'      => false,
			'source_field_id'      => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// carry on
		add_filter( "gform_save_field_value_{$this->_args['form_id']}_{$this->_args['nested_form_field_id']}", array( $this, 'refresh_value_on_save' ), 10, 5 );

	}

	public function refresh_value_on_save( $value, $entry, $field, $form, $input_id ) {

		$child_entry_ids = explode( ',', $value );
		if ( empty( $child_entry_ids ) ) {
			return $value;
		}

		$target_child_entry = GFAPI::get_entry( array_pop( $child_entry_ids ) );
		if ( is_wp_error( $target_child_entry ) ) {
			return $value;
		}

		$target_value = rgar( $target_child_entry, $this->_args['source_field_id'] );
		GFAPI::update_entry_field( $entry['id'], $this->_args['target_field_id'], $target_value );

		return $value;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

}
