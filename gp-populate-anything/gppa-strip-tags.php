<?php
/**
 * Gravity Perks // Populate Anything // Strip Tags from Populated Value
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_process_template_value', function( $template_value, $field ) {

	// Update "123" to your form ID and "4" to your field ID.
	if ( $field->formId == 123 and $field->id == 4 ) {
		$template_value = strip_tags( $template_value );
	}

	return $template_value;
}, 10, 7 );
