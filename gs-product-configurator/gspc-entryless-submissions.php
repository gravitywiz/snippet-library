<?php
/**
 * Gravity Shop // Product Configurator // Allow Entry-less Submissions
 * https://gravitywiz.com/documentation/gs-product-configurator/
 *
 * Bypass Gravity Forms default validation that requires at least one field to be filled out to submit a form.
 *
 * Note: An empty entry is still created with this snippet. If truly entry-less submissions become a popular request, we
 * will revisit.
 */
add_filter( 'gform_validation', function( $result ) {
	if ( rgpost( 'gspc_product_price' ) && ! $result['is_valid'] && $result['form']['fields'][0]['validation_message'] == esc_html__( 'At least one field must be filled out', 'gravityforms' ) ) {
		$result['is_valid'] = true;
		foreach ( $result['form']['fields'] as &$field ) {
			$field->failed_validation  = false;
			$field->validation_message = '';
		}
		add_filter( 'gform_abort_submission_with_confirmation', '__return_true' );
	}
	return $result;
} );
