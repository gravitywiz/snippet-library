<?php
/**
 * Gravity Wiz // Gravity Forms // Force Numeric Keyboard for Number Fields on Mobile
 * https://gravitywiz.com/
 *
 * This snippet forces the numeric keyboard for Number fields on mobile devices by updating the "type" attribute of the
 * input to "tel" instead of "number". If you find there are any drawbacks to this approach, let us know!
 */
add_filter( 'gform_field_content', function( $content, $field ) {
	if ( $field->get_input_type() === 'number' ) {
		$content = str_replace( 'type=\'number\'', 'type=\'tel\'', $content );
	}
	return $content;
}, 10, 2 );
