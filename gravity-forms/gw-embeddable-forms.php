<?php
/**
 *
 * Gravity Wiz // Gravity Forms // Embeddable Forms
 *
 * Combine multiple forms to create a single form.
 *
 * WARNING! This is an experimental snippet. You will likely encounter bugs for which we may not be able to provide support.
 *
 * @version   0.1
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/
 */
class GW_Embeddable_Form {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'         => false,
			'nested_form_ids' => array(),
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// carry on
		add_filter( 'gform_form_post_get_meta', array( $this, 'maybe_combine_forms' ) );

	}

	public function maybe_combine_forms( $form ) {

		if ( (int) $form['id'] !== (int) $this->_args['form_id'] || $this->is_form_editor() ) {
			return $form;
		}

		remove_filter( 'gform_form_post_get_meta', array( $this, 'maybe_combine_forms' ) );

		foreach ( $this->_args['nested_form_ids'] as $index => $form_id ) {
			$nested_form = GFAPI::get_form( $form_id );
			if ( $nested_form ) {
				$fields         = $this->get_normalized_fields( $nested_form, $form, $index + 1 );
				$form['fields'] = array_merge( $form['fields'], $fields );
			}
		}

		add_filter( 'gform_form_post_get_meta', array( $this, 'maybe_combine_forms' ) );

		return $form;
	}

	/**
	 * Returns an array of fields prepared for inclusion on the target form.
	 *
	 * + Field IDs are prefixed with a positional index (i.e. 1, 2, 3, 4).
	 * + Input IDs for input-based fields are updated with the adjusted field ID
	 * + The "formId" property is updated to that of the target form
	 *
	 * @param $form
	 * @param $target_form
	 * @param $prefix
	 *
	 * @return mixed
	 */
	public function get_normalized_fields( $form, $target_form, $prefix ) {

		$base   = $prefix * 1000; // allows up to 1,000 fields per form; not an artificial limit
		$fields = $form['fields'];

		foreach ( $fields as &$field ) {

			$orig_field_id   = $field['id'];
			$field['id']    += $base;
			$field['formId'] = $target_form['id'];

			$inputs = $field['inputs'];

			if ( is_array( $field['inputs'] ) ) {
				$inputs = $field['inputs'];
				foreach ( $inputs as &$input ) {
					$input['id'] = str_replace( "{$orig_field_id}.", "{$field['id']}.", $input['id'] );
				}
			}

			$field['inputs'] = $inputs;

		}

		return $fields;
	}

	public function is_form_editor() {
		return GFForms::get_page() === 'form_editor';
	}

}

# Configuration

new GW_Embeddable_Form( array(
	'form_id'         => 123,
	'nested_form_ids' => array( 124, 125 ),
) );
