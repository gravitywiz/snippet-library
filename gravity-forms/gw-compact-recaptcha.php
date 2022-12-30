<?php
/**
 * Gravity Wiz // Gravity Forms // Update reCAPTCHA widget to use "compact" mode.
 * https://gravitywiz.com/
 */
// Update "123" to your form ID and "4" to your reCAPTCHA field ID.
add_filter( 'gform_field_content_123_4', function( $field_content ) {
	$search        = 'data-theme=';
	$replace       = sprintf( 'data-size=\'compact\' \0', $field_content );
	$field_content = preg_replace( "/$search/", $replace, $field_content, 1 );
	return $field_content;
} );
