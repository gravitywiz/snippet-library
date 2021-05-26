<?php
/**
 * Gravity Perks // Populate Anything // Unserialize into Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function ( $choices, $field, $objects ) {

	$new_choices = array();
	foreach ( $choices as $choice ) {
		$values = unserialize( $choice['value'] );
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
