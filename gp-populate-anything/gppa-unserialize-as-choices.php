<?php
/**
 * Gravity Perks // Populate Anything // Unserialize as Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * If you have a serialized value mapped to a choice-based field, Populate Anything will populate it as a single value.
 * This snippet adds the `gppa-unserialize-as-choices` designator to allow you to specify when serialized values
 * should be converted into choices.
 *
 * Usage:
 *
 * 1. Install this code as a plugin or as a snippet.
 * 2. Add the `gppa-unserialize-as-choices` designator to your field's CSS Class Name setting.
 *
 * Plugin Name:  GP Populate Anything – Unserialize as Choices
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Convert serialized data into choices when populated into a choice-based field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gppa_input_choices', function ( $choices, $field ) {

	if ( strpos( $field->cssClass, 'gppa-unserialize-as-choices' ) === false ) {
		return $choices;
	}

	$new_choices = array();

	foreach ( $choices as $choice ) {
		$values = maybe_unserialize( $choice['value'] );
		if ( ! $values ) {
			continue;
		}
		foreach ( $values as $value ) {
			$new_choices[] = array(
				'text'       => $value,
				'value'      => $value,
				'isSelected' => false,
			);
		}
	}

	return $new_choices;
}, 10, 2 );
