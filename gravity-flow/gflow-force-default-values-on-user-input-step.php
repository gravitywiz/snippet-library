<?php
/**
 * Gravity Forms // Gravity Flow // Force Default Values on User Input Step
 * https://gravitywiz.com/
 */
add_filter( 'gravityflow_field_value_entry_editor', function( $value, $field ) {
	// Update "123" to your form ID and "4" and "5" to field IDs that should be forced to use their default values.
	if ( $field->formId == 123 && in_array( $field->id, array( 4, 5 ) ) ) {
		$value = $field->get_value_default_if_empty( array() );
	}
	return $value;
}, 10, 2 );
