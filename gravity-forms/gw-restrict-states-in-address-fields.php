<?php
/**
 * Gravity Wiz // Gravity Forms // Restrict States in Address Fields
 * https://gravitywiz.com/
 *
 * Restrict the states that can be selected for Address fields. Either restrict specific fields or restrict all Address
 * fields on the site.
 *
 * See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/ for details on how to install this snippet.
 *
 * @version 1.1
 * @license GPL-2.0+
 * @link    http://gravitywiz.com
 */
class GW_Restrict_States_In_Address_Field {

	public function __construct( $args = array() ) {
		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args(
			$args,
			array(
				'form_id'            => false,
				'field_id'           => false,
				'allowed_states'     => null,
				'validation_message' => 'We\'re sorry, we only offer our services in the following states: %s',
			)
		);

		if ( ! $this->_args['allowed_states'] ) {
			return;
		}

		add_filter( 'gform_validation', array( $this, 'validate' ) );
		add_filter( 'gform_field_input', array( $this, 'register_us_states_filter' ), 10, 2 );
		add_filter( 'gform_field_content', array( $this, 'unregister_us_states_filter' ) );

	}


	/**
	 * @param array $result Gravity Forms validation result.
	 */
	public function validate( $result ) {
		$form = $result['form'];

		// Do not validate the states unless the form ID matches or if no form ID was supplied which means we validate
		// for all forms.
		if ( ! $this->is_applicable_form( $form ) ) {
			return $result;
		}

		foreach ( $form['fields'] as &$field ) {

			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$field_value    = GFFormsModel::get_field_value( $field );
			$selected_state = rgar( $field_value, "{$field->id}.4" );

			if ( ! $selected_state ) {
				continue;
			}

			if ( in_array( $selected_state, $this->_args['allowed_states'], true )
			  || array_key_exists( $selected_state, $this->_args['allowed_states'])) {
				continue;
			}

			$allowed_states_list = join( ', ', $this->_args['allowed_states'] );

			$field['failed_validation']  = true;
			$field['validation_message'] = sprintf( $this->_args['validation_message'], $allowed_states_list );
			$result['is_valid']          = false;
		}

		$result['form'] = $form;

		return $result;
	}

	/**
	 * Register our states filter immediately before the field's input markup is generated.
	 *
	 * @param $return
	 * @param $field
	 *
	 * @return mixed
	 */
	public function register_us_states_filter( $return, $field ) {
		if ( $this->is_applicable_field( $field ) ) {
			add_filter( 'gform_us_states', array( $this, 'filter_states' ) );
		}
		return $return;
	}

	/**
	 * Unregister our states filter after the field's input markup has been generated.
	 *
	 * @param $return
	 *
	 * @return mixed
	 */
	public function unregister_us_states_filter( $return ) {
		remove_filter( 'gform_us_states', array( $this, 'filter_states' ) );
		return $return;
	}

	public function filter_states( $states ) {
		return array_intersect( $this->_args['allowed_states'], $states );
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

	public function is_applicable_field( $field ) {

		if ( ! $this->is_applicable_form( $field->formId ) ) {
			return false;
		}

		// Check if this is our specified field if set.
		if ( isset( $this->_args['field_id'] ) && ! empty( $this->_args['field_id'] ) ) {
			return (int) $field->id === (int) $this->_args['field_id'];
		}

		// Otherwise, all Address fields are applicable.
		return $field->get_input_type() === 'address';
	}

}

// Restrict states for all Address field.
//new GW_Restrict_States_In_Address_Field( array(
//	'allowed_states' => array(
//		'California',
//		'Iowa',
//	),
//) );
// Or when value is different, like using gform_us_states
// (2 letter state values, full state name label).
//new GW_Restrict_States_In_Address_Field( array(
//	'allowed_states' => array(
//		'CA' => 'California',
//		'IA' => 'Iowa',
//	),
//) );

// Restrict states for specific Address field.
new GW_Restrict_States_In_Address_Field( array(
	'form_id'        => 123,
	'field_id'       => 4,
	'allowed_states' => array(
		'California',
		'Iowa',
	),
) );
