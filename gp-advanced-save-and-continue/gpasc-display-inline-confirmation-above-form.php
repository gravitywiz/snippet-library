<?php
/**
 * Gravity Perks // Advanced Save & Continue // Display Inline Confirmation Above the Form
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 */
add_filter( 'gpasc_attach_inline_confirmation_message', function( $attached, $confirmation_message, $form_markup ) {
	return $confirmation_message . $form_markup;
}, 10, 3 );
