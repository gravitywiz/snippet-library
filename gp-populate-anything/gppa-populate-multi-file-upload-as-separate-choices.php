<?php
/**
 * Gravity Perks // Populate Anything // Populate Multi-file Upload Images as Separate Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/470b9d0e46f34fc1952135350cffe898
 *
 * Populate a preview of each image uploaded into a Multi-file Upload field as a separate choice in a Radio or Checkbox field.
 */
// Update "123" to your form ID; update "4" to the ID the field you're populating via Populate Anything.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {
	$parsed_choices = array();
	foreach ( $choices as $choice ) {
		$_choices = explode( ', ', $choice['value'] );
		foreach ( $_choices as $_choice ) {
			$parsed_choices[] = array(
				'value' => $_choice,
				'text'  => sprintf( '<img src="%s" width="200">', $_choice ),
			);
		}
	}
	return $parsed_choices;
}, 10, 3 );
