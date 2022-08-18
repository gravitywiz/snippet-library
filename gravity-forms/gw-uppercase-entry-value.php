<?php
/**
 * Gravity Wiz // Gravity Forms // Uppercase Entry Value (by Field)
 * https://gravitywiz.com/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gform_get_input_value_123_4', function( $value ) {
	return strtoupper( $value );
} );
