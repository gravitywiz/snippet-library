<?php
/**
 * Gravity Wiz // Gravity Forms // Convert to 2-Letter Abbreviation
 * http://gravitywiz.com/
 *
 * Convert the submitted state to its 2-letter abbreviation on submission (e.g. Virginia → VA). Invalid states will
 * return a validation error.
 *
 * Instruction Video: https://www.loom.com/share/28ec68e9a627493191c2fbd4882f53a5
 *
 * Instructions:
 *
 *  1. Update the "form_id" parameter to your form ID.
 *  2. Update the "field_id" parameter to your Address field ID.
 */
class GW_Convert_State_to_Abbr {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_field_validation', array( $this, 'validate_state' ), 10, 4 );
		add_action( 'gform_pre_submission', array( $this, 'convert_state' ) );

	}

	public function validate_state( $result, $value, $form, $field ) {

		if ( ! $this->is_applicable_form( $field->formId ) || (int) $field->id !== (int) $this->_args['field_id'] ) {
			return $result;
		}

		$country_value = $value[ "{$field->id}.6" ];
		if ( $country_value !== 'United States' ) {
			return $result;
		}

		$address_field = new GF_Field_Address();

		$state_value = rgpost( "input_{$field->id}_4" );
		if ( in_array( $state_value, $address_field->get_us_states() ) ) {
			return $result;
		}

		$result['is_valid'] = false;
		$result['message']  = esc_html__( 'Please enter a valid US state.' );

		return $result;
	}

	public function convert_state( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$field = GFAPI::get_field( $form, $this->_args['field_id'] );
		if ( ! $field ) {
			return;
		}

		$country_value = rgpost( "input_{$field->id}_6" );
		if ( $country_value !== 'United States' ) {
			return;
		}

		$address_field = new GF_Field_Address();

		$state_value = rgpost( "input_{$field->id}_4" );
		if ( ! in_array( $state_value, $address_field->get_us_states() ) ) {
			return;
		}

		$state_code = $address_field->get_us_state_code( $state_value );
		if ( ! $state_code ) {
			return;
		}

		$_POST[ "input_{$field->id}_4" ] = $state_code;

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Configuration

new GW_Convert_State_to_Abbr( array(
	'form_id' => 123,
	'field_id' => 4,
) );
