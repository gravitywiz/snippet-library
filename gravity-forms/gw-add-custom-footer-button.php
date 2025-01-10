<?php
/**
 * Gravity Wiz // Gravity Forms // Add Custom Footer Button after Save & Continue
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 */
// Update "123" to your form ID.
add_filter( 'gform_savecontinue_link_123', function( $markup, $form ) {
	return $markup . GFFormDisplay::get_form_button( $form['id'], "my_custom_button_{$form['id']}", array( 'type' => 'button' ), 'My Custom Button', 'gform_previous_button', 'My Custom Button', false );
}, 10, 2 );
