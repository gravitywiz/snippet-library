<?php
/**
 * Gravity Perks // Nested Forms // Nested Form Field Listener
 *
 * Add a form field that will listen for changes in a Nested Form field and update to pull the value of a designated
 * child form field.
 *
 * # Current Limitations
 *
 * 1. The value will only be retrieved from the last submitted child entry in the designed Nested Form field.
 *
 * @version  0.2
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Plugin Name:  GPNF Listener Field
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Add a form field that will listen for changes in a Nested Form field and update to pull the value of a designated child form field.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   http://gravitywiz.com
 */
class GPNF_Listener_Field {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'              => false,
			'nested_form_field_id' => false,
			'target_field_id'      => false,
			'source_field_id'      => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( "gform_entry_post_save_{$this->_args['form_id']}", array( $this, 'refresh_value_on_save' ), 10, 2 );

		add_action( 'gform_register_init_scripts', array( $this, 'register_init_script' ) );

	}

	public function register_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$script = "
			( function( $ ) {

				var formId        = {$form['id']},
					nestedFieldId = {$this->_args['nested_form_field_id']},
					targetFieldId = '{$this->_args['target_field_id']}',
					sourceFieldId = '{$this->_args['source_field_id']}',
					bound         = false;

				// GPNF's instance is only guaranteed to exist once the child form initializes, which always happens
				// before an entry can be added.
				gform.addAction( 'gpnf_init_nested_form', function( nestedFormId, gpnf ) {

					if ( bound || gpnf.formId != formId || gpnf.fieldId != nestedFieldId || ! gpnf.viewModel ) {
						return;
					}

					bound = true;

					gpnf.viewModel.entries.subscribe( function( entries ) {

						var value = '';

						if ( entries.length ) {
							value = entries[ entries.length - 1 ][ 'f' + sourceFieldId ];
							value = value && typeof value === 'object' ? value.value : value;
						}

						$( '#input_' + formId + '_' + targetFieldId.split( '.' ).join( '_' ) )
							.val( value == null ? '' : value )
							.trigger( 'change' );

					} );

				} );

			} )( jQuery );
		";

		GFFormDisplay::add_init_script( $form['id'], 'gpnf_listener_field_' . $this->_args['target_field_id'], GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function refresh_value_on_save( $entry, $form ) {

		$value           = rgar( $entry, (string) $this->_args['nested_form_field_id'], rgpost( 'input_' . $this->_args['nested_form_field_id'] ) );
		$child_entry_ids = array_filter( array_map( 'trim', explode( ',', (string) $value ) ) );

		if ( empty( $child_entry_ids ) ) {
			return $entry;
		}

		$target_child_entry = GFAPI::get_entry( array_pop( $child_entry_ids ) );
		if ( is_wp_error( $target_child_entry ) ) {
			return $entry;
		}

		$target_value = rgar( $target_child_entry, (string) $this->_args['source_field_id'] );

		GFAPI::update_entry_field( $entry['id'], (string) $this->_args['target_field_id'], $target_value );

		$entry[ (string) $this->_args['target_field_id'] ] = $target_value;

		return $entry;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

}

new GPNF_Listener_Field( array(
	'form_id'              => 53,
	'nested_form_field_id' => 1,
	'target_field_id'      => 2,
	'source_field_id'      => 2,
) );
