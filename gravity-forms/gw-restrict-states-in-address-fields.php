<?php
/**
 * Gravity Wiz // Gravity Forms // Restrict States in Address Fields
 *
 * Restrict the states that can be selected for Address fields. Either restrict specific fields or restrict all Address
 * fields on the site.
 *
 * See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/ for details on how to install this snippet. 
 *
 * @version 1.0
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
	}


	/**
	 * @param array $result Gravity Forms validation result.
	 */
	public function validate( $result ) {
		$form = $result['form'];

		// Do not validate the states unless the form ID matches or if no form ID was supplied which means we validate
		// for all forms.
		if ( $this->_args['form_id'] && $form['id'] !== $this->_args['form_id'] ) {
			return $result;
		}

		foreach ( $form['fields'] as &$field ) {
			$field_value = GFFormsModel::get_field_value( $field );

			if ( $field->type !== 'address' ) {
				continue;
			}

			$selected_state = rgar( $field_value, "{$field->id}.4" );

			if ( ! $selected_state ) {
				continue;
			}

			if ( in_array( $selected_state, $this->_args['allowed_states'], true ) ) {
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
}

// Restrict states for all Address field.
//new GW_Restrict_States_In_Address_Field( array(
//	'allowed_states' => array(
//		'California',
//		'Iowa',
//	)
//) );

// Restrict states for specific Address field.
new GW_Restrict_States_In_Address_Field( array(
	'form_id'        => 1,
	'field_id'       => 2,
	'allowed_states' => array(
		'California',
		'Iowa',
	)
) );
