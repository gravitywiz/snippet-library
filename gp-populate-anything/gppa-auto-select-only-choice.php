<?php
/**
 * Gravity Perks // Populate Anything // Auto-select Only Choice
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/e72a9b2a8c114a4588647910cbc65f6f
 *
 * If there is only one choice populated into a field, use this snippet to automatically select it.
 */
// Update "123" to your form ID and "4" to your field ID – or – remove "_123_4" to apply to all forms and fields.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {

	if ( count( $choices ) === 1 && $choices[0]['value'] ) {
		$choices[0]['isSelected'] = true;
	}

	return $choices;
}, 10, 3 );
