<?php
/**
 * Gravity Perks // Advanced Select // Load Advanced Select Globally
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Enable Advanced Select on all supported fields automatically.
 * 
 * Instructions:
 *
 * 1. Install this snippet by following the instructions here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Customize the form_id property at the bottom of this snippet.
 */

class GPASVS_Enable_Globally {

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
		) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

	public function init() {

		if ( ! is_callable( 'gp_advanced_select' ) ) {
			return;
		}

		add_filter( 'gform_pre_render', array( $this, 'enable_gpadvs_for_all_fields' ) );
		add_filter( 'gform_pre_validation', array( $this, 'enable_gpadvs_for_all_fields' ) );
	}

	public function enable_gpadvs_for_all_fields( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( in_array( $field->get_input_type(), gp_advanced_select()->is_supported_input_type() ) ) {
				$field->gpadvsEnable = true;
			}
		}
		return $form;
	}
}

# Configuration

// For All Forms
new GPASVS_Enable_Globally();

// For a Specific Form
// new GPASVS_Enable_Globally( array(
// 	'form_id' => 123,
// ) );
