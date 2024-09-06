<?php
/**
 * Gravity Perks // Populate Anything + Nested Forms // Force Dynamic Population When Editing a Child Entry via GravityView
 * http://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
class GPPA_GPNF_GV_Force_Repopulation_On_Edit {

	private $_args = array();

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

		add_filter( "gpnf_populated_entry_{$this->_args['parent_form_id']}_{$this->_args['nested_form_field_id']}", function( $entry, $form ) {

			if ( ! isset( $GLOBALS['gppa-field-values'] ) ) {
				return $entry;
			}

			$session = new GPNF_Session( $this->_args['parent_form_id'] );
			if ( ! rgar( $session->get_cookie(), 'is_gv_edit' ) ) {
				return $entry;
			}

			$field               = GFAPI::get_field( $form, $this->_args['child_field_id'] );
			$hydrated_field      = gp_populate_anything()->hydrate_field( $field, $form, array(), null, false );
			$entry[ $field->id ] = $hydrated_field['field_value'];

			return $entry;
		}, 10, 2 );

		add_filter( "gpnf_session_script_data_{$this->_args['parent_form_id']}", function( $data ) {
			$data['is_gv_edit'] = is_callable( 'gravityview_get_context' ) && gravityview_get_context() === 'edit';
			return $data;
		} );

	}

}

# Configuration

new GPPA_GPNF_GV_Force_Repopulation_On_Edit( array(
	'parent_form_id'       => 123,
	'nested_form_field_id' => 4,
	'child_field_id'       => 5,
) );
