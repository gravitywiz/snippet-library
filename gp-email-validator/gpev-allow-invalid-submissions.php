<?php
/**
 * Gravity Perks // Email Validator // Allow Invalid Submissions
 * https://gravitywiz.com/documentation/gravity-forms-email-validator/
 *
 * Allow form submissions to go through regardless of email validation status, while still preserving
 * the original validation results (status, reasons, and technical details) in entry meta.
 */
$gpev_failures = [];

// Update "123" to your form ID and "4" to your Email field ID.
add_filter( 'gpev_validation_result_123_4', function( $validation_result, $value, $field, $form, $validator ) use ( &$gpev_failures ) {
	if ( ! $validation_result ) {
		return $validation_result;
	}

	if ( ! $validation_result->is_valid() ) {
		$form_id  = rgar( $form, 'id' );
		$field_id = $field->id ?? null;

		if ( $form_id && $field_id ) {
			$gpev_failures[ $form_id ][ $field_id ] = true;
		}
	}

	return $validation_result;
}, 10, 5 );

// Update "123" to your form ID and "4" to your Email field ID.
add_filter( 'gform_field_validation_123_4', function( $result, $value, $form, $field ) use ( &$gpev_failures ) {
	if ( ! gp_email_validator()->is_email_validator_field( $field ) ) {
		return $result;
	}

	$form_id  = rgar( $form, 'id' );
	$field_id = $field->id ?? null;

	if ( $form_id && $field_id && ! empty( $gpev_failures[ $form_id ][ $field_id ] ) ) {
		$result['is_valid'] = true;
		$result['message']  = '';
	}

	return $result;
}, 11, 4 );
