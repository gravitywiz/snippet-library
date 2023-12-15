<?php
/**
 * Gravity Wiz // Gravity Forms // Capitalize Submitted Data
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/dff6d63f5a9c44938fdc0506ce5d4965
 *
 * Capitializes all words in Single Line Text, Paragraph Text, Address and Name fields.
 */
// Update "123" to the ID of the Form
add_action( 'gform_pre_submission_123', 'gw_capitalize_submitted_data' );
function gw_capitalize_submitted_data( $form ) {

	$applicable_input_types = array( 'address', 'text', 'textarea', 'name' );

	foreach ( $form['fields'] as $field ) {

		$input_type = GFFormsModel::get_input_type( $field );

		if ( ! in_array( $input_type, $applicable_input_types ) ) {
			continue;
		}

		if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {
			foreach ( $field['inputs'] as $input ) {
				$input_key           = sprintf( 'input_%s', str_replace( '.', '_', $input['id'] ) );
				$_POST[ $input_key ] = ucwords( strtolower( rgpost( $input_key ) ) );
			}
		} else {
			$input_key           = sprintf( 'input_%s', $field['id'] );
			$_POST[ $input_key ] = ucwords( strtolower( rgpost( $input_key ) ) );
		}
	}

	return $form;
}
