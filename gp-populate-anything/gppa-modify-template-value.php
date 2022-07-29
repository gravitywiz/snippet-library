<?php
/**
 * Gravity Perks // GP Populate Anything // Modify Template Value
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
add_filter( 'gppa_process_template_value', function( $template_value, $field, $template_name, $populate, $object, $object_type, $objects ) {

	// Update "123" to your form ID and "4" to your field ID.
	if( $field->formId == 123 and $field->id == 4 ) {
		$template_value = 'My modified value.';
	}

	return $template_value;
}, 10, 7 );
