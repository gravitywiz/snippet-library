<?php
/**
 * Gravity Perks // Email Validator // Allow Invalid Submissions
 * https://gravitywiz.com/documentation/gravity-forms-email-validator/
 *
 * Allow form submissions to go through regardless of email validation status, while still preserving
 * the original validation results (status, reasons, and technical details) in entry meta.
 */
add_filter( 'gpev_validation_result', function( $validation_result, $value, $field, $form, $validator ) {
	if ( ! $validation_result ) {
		return $validation_result;
	}

	$validation_result->mark_valid();

	return $validation_result;
}, 10, 5 );
