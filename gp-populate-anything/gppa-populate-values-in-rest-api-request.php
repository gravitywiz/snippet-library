<?php
/**
 * Gravity Perks // Populate Anything // Populate Values in REST API Request
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Populates dynamically populated values if a value is not explicitly provided
 * for fields that have values populated by Populate Anything.
 */
add_action( 'rest_api_init', function () {
	if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
		return;
	}

	add_filter( 'gform_pre_submission_filter', function ( $form ) {
		$field_values = gp_populate_anything()->get_posted_field_values( $form );

		foreach ( $form['fields'] as &$field ) {
			$value = gp_populate_anything()->get_input_values( $field, 'value', $field_values );

			if ( $value && empty( $_POST[ 'input_' . $field->id ] ) ) {
				$_POST[ 'input_' . $field->id ] = $value;
			}
		}

		return $form;
	} );
} );
