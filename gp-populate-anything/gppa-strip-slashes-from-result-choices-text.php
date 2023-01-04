<?php
/**
 * Gravity Perks // Populate Anything // Strip slashes from result choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */


// replace "1" with the ID of the form you want this to apply to and "2" with the ID of the field you want this to apply to.
// you can optionally remove "_1_2" from the filter name (leaving you with just "gppa_input_choice") to automatically apply this to all forms and fields.
add_filter( 'gppa_input_choice_1_2', function( $choice, $field, $object, $objects ) {
	$choice['text'] = stripslashes( $choice['text'] );
	return $choice;
}, 10, 4 );
