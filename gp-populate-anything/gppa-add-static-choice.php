<?php
/**
 * Gravity Perks // Populate Anything // Add a Static Choice
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/27e2a0f006e04b25b2391ff8bc55d286
 *
 */
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {

	array_unshift( $choices, array(
		'text'       => 'My Static Choice',
		'value'      => 'static',
		'isSelected' => false,
	) );

	return $choices;
}, 10, 3 );
