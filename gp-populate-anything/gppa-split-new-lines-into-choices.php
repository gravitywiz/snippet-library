<?php
/**
 * Gravity Perks // Populate Anything // Split New Lines into Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/3a48db6585f842ffbc4e25286e104aa9
 *
 * Split new lines into separate choices when populating from Paragraph fields (or any other value that may contain new lines).
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function ( $choices, $field, $objects ) {
	$new_choices = array();
	foreach ( $choices as $choice ) {
		$values = array_map( 'trim', explode( "\n", $choice['value'] ) );
		foreach ( $values as $value ) {
			$new_choices[] = array(
				'text'       => $value,
				'value'      => $value,
				'isSelected' => false,
			);
		}
	}

	return $new_choices;
}, 10, 3 );
