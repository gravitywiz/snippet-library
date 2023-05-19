<?php
/**
 * Gravity Perks // Populate Anything // Format Numeric Choice Labels as Currency
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {
	foreach ( $choices as &$choice ) {
		$choice['text'] = GFCommon::to_money( $choice['text'] );
	}
	return $choices;
}, 10, 3 );
