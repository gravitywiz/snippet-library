<?php
/**
 * Gravity Perks // Limit Choices // Shared Choices
 *
 * Share limits across choices in the same field.
 *
 * @version  1.0
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/documentation/gravity-forms-limit-choices/
 *
 * Plugin Name:  GP Limit Choices — Shared Choices
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-limit-choices/
 * Description:  Share limits across choices in the same field.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   http://gravitywiz.com
 */
class GPLC_Shared_Choices {

	public $_args = array();

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

		$shared_count = 0;

		if ( is_array( $counts ) ) {
			foreach ( $counts as $count ) {
				$shared_count += $count;
			}
		}

		if ( is_array( $field->choices ) ) {
			foreach ( $field->choices as $choice ) {
				if ( isset( $choice['limit'] ) && isset( $choice['value'] ) ) {
					$counts[ $choice['value'] ] = $shared_count;
				}
			}
		}

		return $counts;
	}
}

# Configuration

new GPLC_Shared_Choices( array(
	'form_id'  => 123,
	'field_id' => 4,
) );
