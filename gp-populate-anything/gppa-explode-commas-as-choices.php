<?php
/**
 * Gravity Perks // Populate Anything // Explode Commas into Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Plugin Name:  GPPA Explode Commas into Choices
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Convert comma-delimited values into choices when populated into a choice-based field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 *
 * If you have an array-like value mapped to a choice-based field, Populate Anything does not yet know when it should be
 * converted to choices or imploded into a string so it defaults to the latter.
 *
 * This snippet adds the `gppa-explode-commas-into-choices` designator to all you to specify when array-like values
 * should be converted into choices.
 *
 * Usage:
 *
 * 1. Install this code as a plugin or as a snippet.
 * 2. Add the `gppa-explode-commas-into-choices` designator to your field's CSS Class Name setting.
 */
add_filter( 'gppa_input_choices', function ( $choices, $field, $objects ) {
	if ( strpos( $field->cssClass, 'gppa-explode-commas-into-choices' ) === false ) {
		return $choices;
	}
	$new_choices = array();
	foreach ( $choices as $choice ) {
		$values = explode( ',', $choice['value'] );
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
