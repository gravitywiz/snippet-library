<?php
/**
 * Gravity Perks // Populate Anything // Get Value Selected in Source Field When Populating Choices in Target Field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to the ID of field for which you would like to customize the populated choices.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {
	// Update "5" to the ID of the field for which you would like to check the selected value.
	$selected_value = $GLOBALS['gppa-field-values'][ $field->formId ][5];
	return $choices;
}, 10, 3 );
