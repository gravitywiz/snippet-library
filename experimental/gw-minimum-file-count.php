<?php
/**
 * Gravity Wiz // Gravity Forms // Require a minimum number of files in multi file uploader
 * https://gravitywiz.com/
 */
add_filter( 'gform_validation', function ( $result ) {
	$form = $result['form'];

	/* Customize the following variables. */
	$minimum_number_of_files = 4;
	$form_id_to_validate     = 2;
	$field_id_to_validate    = 1;
	$validation_message      = 'Minimum number of files not met.';
	/* End customizing */

	if ( $form['id'] !== $form_id_to_validate ) {
		return $result;
	}

	foreach ( $form['fields'] as &$field ) {

		if ( $field->id !== $field_id_to_validate ) {
			continue;
		}

		if ( empty( GFFormsModel::$uploaded_files[ $form['id'] ] ) ) {
			continue;
		}

		$input_name     = 'input_' . $field->id;
		$uploaded_files = GFFormsModel::$uploaded_files[ $form['id'] ][ $input_name ];

		if ( count( $uploaded_files ) < $minimum_number_of_files ) {
			$field['failed_validation']  = true;
			$field['validation_message'] = $validation_message;
			$result['is_valid']          = false;
		}

	}

	$result['form'] = $form;

	return $result;
} );
