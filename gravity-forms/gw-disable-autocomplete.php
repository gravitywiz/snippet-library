<?php
/**
 * Gravity Wiz // Gravity Forms // Disable Auto-complete
 *
 * Disable browser auto-complete.
 *
 * @version 0.2
 * @author  Scott Buchmann <scott@gravitywiz.com>
 * @license GPL-2.0+
 * @link    http://gravitywiz.com
 */
// Disable auto-complete on form.
add_filter( 'gform_form_tag', function( $form_tag ) {
	return str_replace( '>', ' autocomplete="password" name="notASearchField">', $form_tag );
}, 11 );

// Diable auto-complete on each field.
add_filter( 'gform_field_content', function( $input ) {
	return preg_replace( '/<(input|textarea)/', '<${1} autocomplete="password" name="notASearchField"', $input );
}, 11 );
