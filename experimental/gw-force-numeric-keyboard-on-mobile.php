<?php
/**
 * Gravity Wiz // Gravity Forms // Force Numeric Keyboard for Number Fields on Mobile
 * https://gravitywiz.com/
 *
 * This snippet forces the numeric keyboard for Number fields on mobile devices by updating the "type" attribute of the
 * input to "tel" instead of "number". If you find there are any drawbacks to this approach, let us know!
 *
 * Known Limitations
 *
 * 1. This will not work if you're collecting decimal numbers as mobile numeric keyboards do not support decimals or commas.
 */
add_filter( 'gform_field_content', function( $content, $field ) {
	if ( $field->get_input_type() === 'number' ) {
		$content = preg_replace( '/type=\'(number|text)\'/', 'type=\'tel\'', $content );
	}
	return $content;
}, 10, 2 );
