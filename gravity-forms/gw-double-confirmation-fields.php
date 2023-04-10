<?php
/**
 * Gravity Wiz // Gravity Forms // Double Confirmation Fields
 * https://gravitywiz.com/custom-field-confirmation/
 *
 * Instruction Video: https://www.loom.com/share/1df75e10f7fb404ebbeec37c7d19531e
 *
 * Require a field's value to be entered twice to confirm it.
 */
add_filter( 'gform_validation', 'gfcf_validation' );
function gfcf_validation( $validation_result ) {
	global $gfcf_fields;

	$form          = $validation_result['form'];
	$confirm_error = false;

	if ( ! isset( $gfcf_fields[ $form['id'] ] ) ) {
		return $validation_result;
	}

	foreach ( $gfcf_fields[ $form['id'] ] as $confirm_fields ) {

		$values = array();

		// loop through form fields and gather all field values for current set of confirm fields
		foreach ( $form['fields'] as $confirm_field ) {
			if ( ! in_array( $confirm_field['id'], $confirm_fields ) ) {
				continue;
			}

			$values[] = rgpost( "input_{$confirm_field['id']}" );

		}

		// filter out unique values, if greater than 1, a value was different
		if ( count( array_unique( $values ) ) <= 1 ) {
			continue;
		}

		$confirm_error = true;

		foreach ( $form['fields'] as &$field ) {
			if ( ! in_array( $field['id'], $confirm_fields ) || RGFormsModel::is_field_hidden( $form, $confirm_field, array() ) ) {
				continue;
			}

			// fix to remove phone format instruction
			if ( RGFormsModel::get_input_type( $field ) == 'phone' ) {
				$field['phoneFormat'] = '';
			}

			$field['failed_validation']  = true;
			$field['validation_message'] = 'Your values do not match.';
		}
	}

	$validation_result['form']     = $form;
	$validation_result['is_valid'] = ! $validation_result['is_valid'] ? false : ! $confirm_error;

	return $validation_result;
}

function register_confirmation_fields( $form_id, $fields ) {
	global $gfcf_fields;

	if ( ! $gfcf_fields ) {
		$gfcf_fields = array();
	}

	if ( ! isset( $gfcf_fields[ $form_id ] ) ) {
		$gfcf_fields[ $form_id ] = array();
	}

	$gfcf_fields[ $form_id ][] = $fields;
}

register_confirmation_fields( 1, array( 2, 3 ) );
