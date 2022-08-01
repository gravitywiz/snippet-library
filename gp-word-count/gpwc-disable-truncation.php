<?php
/**
 * Gravity Perks // Word Count // Disable Truncation
 * https://gravitywiz.com/documentation/gravity-forms-word-count/
 *
 * Instruction Video: https://www.loom.com/share/7523ab6283eb4a4c957c95f2e208a42c
 *
 * This will prevent the words in a specific field from being truncated when the limit is reached.
 */
add_filter( 'gpwc_script_args', function( $args, $field ) {
	// Update "123" to your form ID and "4" to your Word-Count-enabled field.
	if ( $field->formId == 123 && $field->id == 4 ) {
		$args['truncate'] = false;
	}
	return $args;
}, 10, 2 );
