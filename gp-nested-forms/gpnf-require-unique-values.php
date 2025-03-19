<?php
/**
 * Gravity Perks // Nested Forms // Require Unique Value Between Parent & Child
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Throw a validation error if a value is present a child entry that has been entered on the parent form.
 *
 * Example: Let's say you're using Nested Forms to register users for an event. The user submitting the form enters
 * their contact information in the parent form and registers attendees via a Nested Form field. You may want to prevent
 * the registering user from entering themselves as an attendee via the Nested Form field. This will allow you to catch
 * these kinds of errors and throw a validation message on submission.
 *
 * Plugin Name:  GP Nested Forms - Require Unique Value Between Parent & Child
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Throw a validation error if a value is present a child entry that has been entered on the parent form.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
class GPNF_Required_Unique {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'parent_form_id'       => 1951,
			'parent_field_id'      => 5,
			'nested_form_field_id' => 1,
			'child_form_field_id'  => 3,
			'validation_message'   => __( 'This value has been entered on the child form. Please update the value of this field or remove the offending child entry.' ),
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_field_validation', array( $this, 'validate' ), 10, 4 );

	}

	public function validate( $result, $value, $form, $field ) {

		if ( ! $this->is_applicable_form( $form ) || ! $this->is_applicable_field( $field ) ) {
			return $result;
		}

		foreach ( $form['fields'] as $field ) {

			if ( $field->get_input_type() != 'form' || $field->id != $this->_args['nested_form_field_id'] ) {
				continue;
			}

			$child_entry_ids = explode( ',', rgpost( 'input_' . $field->id ) );
			foreach ( $child_entry_ids as $child_entry_id ) {
				$child_entry = GFAPI::get_entry( $child_entry_id );
				$child_value = rgar( $child_entry, $this->_args['child_form_field_id'] );
				if ( $child_value == $value ) {
					$result['is_valid'] = false;
					$result['message']  = $this->_args['validation_message'];
					break;
				}
			}
		}

		return $result;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['parent_form_id'] ) || $form_id == $this->_args['parent_form_id'];
	}

	public function is_applicable_field( $field ) {

		$field_id = isset( $field->id ) ? $field->id : $field;

		return empty( $this->_args['parent_field_id'] ) || $field_id == $this->_args['parent_field_id'];
	}

}

# Configuration

new GPNF_Required_Unique( array(
	'parent_form_id'       => 1951,
	'parent_field_id'      => 5,
	'nested_form_field_id' => 1,
	'child_form_field_id'  => 3,
	//'validation_message'   => 'Oops!',
) );
