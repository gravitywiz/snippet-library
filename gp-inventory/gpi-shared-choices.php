<?php
/**
 * Gravity Perks // Inventory // Shared Choices
 *
 * Share limits across choices in the same field.
 *
 * Plugin Name:  GP Inventory — Shared Choices
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-inventory/
 * Description:  Share limits across choices in the same field.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com
 */
class GPI_Shared_Choices {

	public $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
			'choices'  => array(),
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gpi_choice_counts', array( $this, 'modify_choice_counts' ), 10, 3 );

	}

	public function modify_choice_counts( $counts, $form_id, $field ) {

		if ( ! $this->is_applicable_field( $field ) ) {
			return $counts;
		}

		if ( ! empty( $this->_args['choices'] ) ) {
			$shared_count = 0;
			foreach ( $this->_args['choices'] as $value ) {
				$shared_count += rgar( $counts, $value, 0 );
			}
		} else {
			$shared_count = array_sum( $counts );
		}

		foreach ( $field->choices as $choice ) {
			if ( ! isset( $choice['inventory_limit'] ) || ! isset( $choice['value'] ) ) {
				continue;
			}
			if ( ! empty( $this->_args['choices'] ) && ! in_array( $choice['value'], $this->_args['choices'] ) ) {
				continue;
			}
			$counts[ $choice['value'] ] = $shared_count;
		}

		return $counts;
	}

	public function is_applicable_field( $field ) {
		$form_id = isset( $field->formId ) ? $field->formId : null;
		return (int) $form_id === (int) $this->_args['form_id'] && isset( $field->id ) && (int) $field->id === (int) $this->_args['field_id'];
	}

}

# Configuration

new GPI_Shared_Choices( array(
	'form_id'  => 123,
	'field_id' => 4,
	'choices'  => array( 'Second Choice', 'Third Choice' ),
) );
