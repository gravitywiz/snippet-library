<?php
/**
 * Gravity Perks // Blocklist // Exclude Hidden Fields from Blocklist Validation
 * https://gravitywiz.com/documentation/gravity-forms-blocklist/
 */
add_filter( 'gform_validation', function( $result ) {
	foreach ( $result['form']['fields'] as &$field ) {
		if ( $field->get_input_type() !== 'hidden' ) {
			continue;
		}
		$gpb_validation_message = gf_apply_filters( array( 'gpb_validation_message', $result['form']['id'], $field->id ), __( 'We\'re sorry, the text you entered for this field contains blocked words.', 'gp-blocklist' ) );
		if ( $field->validation_message === $gpb_validation_message ) {
			$field->failed_validation = false;
		}
	}
	$result['is_valid'] = true;
	foreach ( $result['form']['fields'] as $field ) {
		if ( $field->failed_validation ) {
			$result['is_valid'] = true;
		}
	}
	return $result;
}, 11 );
