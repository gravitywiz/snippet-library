<?php
/**
 * Gravity Perks // GP Populate Anything // Change No Choices Text
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Replace 123 with your Form Id and 4 with your Field Id
add_filter( 'gppa_no_choices_text', function ( $label, $field ) {
	if ( $field->formId == 123 and $field->id == 4 ) {
		$label = 'No Post(s) Found';
	}
	return $label;
}, 10, 2 );
