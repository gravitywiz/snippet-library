<?php
/**
 * Gravity Wiz // Gravity Forms // Time Sensitive Choices
 *
 * Provide a drop down of times and automatically filter which choices are available based on the current time.
 *
 * @version	  1.1
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/...
 * @copyright 2015 Gravity Wiz
 *
 * # WordPress Plugin Header
 *
 * Plugin Name: Gravity Forms Time Sensitive Choices
 * Plugin URI: http://ounceoftalent.com
 * Description: Provide a drop down of times and automatically filter which choices are avialable based on the current time.
 * Author: David Smith
 * Version: 1.1
 * Author URI: http://ounceoftalent.com
 *
 */
class GW_Time_Sensitive_Choices {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'       => false,
			'field_ids'     => array(),
			'time_mod'      => false,
			'date_field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		add_filter( 'gform_pre_render', array( $this, 'filter_form_by_time' ) );

	}

	public function filter_form_by_time( $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach( $form['fields'] as &$field ) {
			if( $this->is_applicable_field( $field ) ) {
				$field->choices = $this->filter_choices_by_time( $field->choices );
			}
		}

		return $form;
	}

	public function filter_choices_by_time( $choices ) {

		$filtered_choices = array();

		$current_time = current_time( 'timestamp' );

		if ( isset( $this->_args['date_field_id'] ) && ! empty( $this->_args['date_field_id'] ) ) {
			if ( isset( $_POST[ 'input_' . $this->_args['date_field_id'] ] ) ) {
				$date_time_field_posted = strtotime( $_POST['input_' . $this->_args['date_field_id'] ] );
				if ( $date_time_field_posted > $current_time ) {
					return $choices;
				}
			}
		}

		$max_time    = strtotime( '23:59', $current_time );
		$time_cutoff = strtotime( $this->_args['time_mod'], $current_time );

		if( $time_cutoff > $max_time ) {
			$time_cutoff = $max_time;
		}

		foreach( $choices as $choice ) {
			$time = strtotime( $choice['value'], $current_time );
			if( $time > $time_cutoff ) {
				$filtered_choices[] = $choice;
			}
		}

		return $filtered_choices;
	}

	public function is_applicable_form( $form ) {
		return $this->_args['form_id'] == $form['id'];
	}

	public function is_applicable_field( $field ) {
		return in_array( $field->id, $this->_args['field_ids'] );
	}

}

new GW_Time_Sensitive_Choices( array(
	'form_id' => 964,
	'field_ids' => array( 10, 12, 13 ),
	'time_mod' => '+1 hours',
) );

new GW_Time_Sensitive_Choices( array(
	'form_id' => 964,
	'field_ids' => array( 10, 12, 13 ),
	'time_mod' => '+1 hours',
	'date_field_id' => 1,
) );