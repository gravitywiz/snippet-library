<?php
/**
 * Calculate input values after submission to ensure that calculated values are always up-to-date.
 *
 * Works great with Date Time Calculator's :age modifier.
 * https://gravitywiz.com/documentation/gravity-forms-date-time-calculator/#calculating-age
 */
class GW_Calculated_Input_Value {
	protected $_args;

	function __construct( $args ) {
		$this->_args = wp_parse_args( $args );

		$this->add_hook();
	}

	function get_filter_tag() {
		return sprintf( 'gform_get_input_value_%d_%d', $this->_args['form_id'], $this->_args['field_id'] );
	}

	function add_hook() {
		add_filter( $this->get_filter_tag(), array( $this, 'calculate_field' ), 10, 4 );
	}

	function remove_hook() {
		remove_filter( $this->get_filter_tag(), array( $this, 'calculate_field' ) );
	}

	function calculate_field( $value, $entry, $field, $input_id ) {
		$this->remove_hook(); // Prevent recursion
		$form  = GFAPI::get_form( $entry['form_id'] );
		$entry = GFAPI::get_entry( $entry['id'] );
		$this->add_hook();

		if ( ! $form || ! $entry ) {
			return $value;
		}

		return GFCommon::calculate( $field, $form, $entry );
	}
}

new GW_Calculated_Input_Value( array(
	'form_id'  => 413,
	'field_id' => 132,
) );
