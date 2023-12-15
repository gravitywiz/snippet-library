<?php
/**
 * Gravity Perks // Limit Choices // Combo Choices
 *
 * Create choices which share their limits with multiple other choices.
 *
 * For example, choice "10am - 12pm" might share it's limit with choices "10am" and "11am".
 *
 * Known Limitations
 *
 * 1. Currently only supports one combo choice per field.
 *
 * @version  1.0
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/documentation/gravity-forms-limit-choices/
 *
 * Plugin Name:  GP Limit Choices — Combo Choices
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-limit-choices/
 * Description:  Create choices which share their limits with multiple other choices.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   http://gravitywiz.com
 */
class GPLC_Combo_Choices {

	public $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
			'combos'   => array(),
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gplc_choice_counts', array( $this, 'modify_gplc_choice_counts' ), 10, 3 );

	}

	public function is_applicable_field( $field ) {
		$form_id = isset( $field->formId ) ? $field->formId : null;

		return (int) $form_id === (int) $this->_args['form_id'] && isset( $field->id ) && (int) $field->id === (int) $this->_args['field_id'];
	}

	public function modify_gplc_choice_counts( $counts, $form_id, $field ) {

		if ( ! $this->is_applicable_field( $field ) ) {
			return $counts;
		}

		foreach ( $this->_args['combos'] as $combo_value => $target_values ) {
			if ( ! is_array( $target_values ) ) {
				$target_values = array( $target_values );
			}
			$greatest_count = 0;
			foreach ( $target_values as $target_value ) {
				$greatest_count = max( rgar( $counts, $target_value, 0 ), $greatest_count );
			}
			if ( is_array( $field->choices ) ) {
				foreach ( $field->choices as $choice ) {
					if ( ! isset( $counts[ $choice['value'] ] ) ) {
						$counts[ $choice['value'] ] = 0;
					}
					if ( $choice['value'] === $combo_value ) {
						$counts[ $choice['value'] ] += $greatest_count;
					} elseif ( in_array( $choice['value'], $target_values, true ) ) {
						$counts[ $choice['value'] ] += rgar( $counts, $combo_value, 0 );
					}
				}
			}
		}

		return $counts;
	}
}

# Configuration

new GPLC_Combo_Choices( array(
	'form_id'  => 123,
	'field_id' => 4,
	'combos'   => array(
		'A & B' => array( 'A', 'B' ),
	),
) );
