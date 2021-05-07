<?php
/** 
 * Gravity Wiz // Gravity Forms // Disable auto-complete on form or on each field
 * https://gravitywiz.com/
 */
// Disable auto-complete on form.
add_filter( 'gform_form_tag', function( $form_tag ) {
	return str_replace( '>', ' autocomplete="off">', $form_tag );
}, 11 );

// Diable auto-complete on each field.
add_filter( 'gform_field_content', function( $input ) {
	return preg_replace( '/<(input|textarea)/', '<${1} autocomplete="off" ', $input );
}, 11 ); 
