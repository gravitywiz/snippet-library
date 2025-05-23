<?php
/**
 * Gravity Perks // Unique ID // Conditional Unique ID for Gravity Flow
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 * 
 * Instruction Video: https://www.loom.com/share/e799e8b4b0984d99a66da79faa34ffee
 *
 * Prevent a Unique ID field from generating its value until a specific Gravity Flow Workflow step is completed.
 * Useful when you want to generate the Unique ID only after approval or other workflow steps.
 *
 * Plugin Name:  Conditional Unique ID for Gravity Flow
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-unique-id/
 * Description:  Prevent a Unique ID field from generating its value until a specific Gravity Flow step is completed.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
class GPUID_Generate_Post_Workflow {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
			'step_id'  => false,
		) );

		if ( ! $this->_args['form_id'] || ! $this->_args['field_id'] || ! $this->_args['step_id'] ) {
			return;
		}

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gpui_unique_id', array( $this, 'prevent_unique_id_generation' ), 10, 3 );
		add_action( 'gravityflow_step_complete', array( $this, 'maybe_generate_unique_id' ), 10, 4 );

	}

	public function prevent_unique_id_generation( $unique, $form_id, $field_id ) {

		if ( $form_id == $this->_args['form_id'] && $field_id == $this->_args['field_id'] && ! gravity_flow()->is_workflow_detail_page() ) {
			return '';
		}

		return $unique;
	}

	public function maybe_generate_unique_id( $step_id, $entry_id, $form_id, $status ) {

		if ( (int) $step_id === $this->_args['step_id'] && (int) $form_id === $this->_args['form_id'] ) {
			$entry = GFAPI::get_entry( $entry_id );
			if ( ! is_wp_error( $entry ) && empty( $entry[ $this->_args['field_id'] ] ) ) {
				$uid_field = GFAPI::get_field( $form_id, $this->_args['field_id'] );
				$uid_field->save_value( GFAPI::get_entry( $entry_id ), $uid_field, false );
			}
		}
	}

}

# Configuration

new GPUID_Generate_Post_Workflow( array(
	'form_id'  => 5,    // Replace with your form ID.
	'field_id' => 3,    // Replace with your Unique ID field ID.
	'step_id'  => 3     // Replace with your Gravity Flow step ID.
) );
