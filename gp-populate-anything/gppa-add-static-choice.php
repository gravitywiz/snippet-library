<?php
/**
 * Gravity Perks // Populate Anything // Add a Static Choice
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/e425398f584148f58fdfea3d6b6f969b
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
