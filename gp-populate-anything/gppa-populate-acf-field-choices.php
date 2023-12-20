<?php
/**
 * Gravity Perks // Populate Anything // Populate Choice-based ACF Field as Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/e3c79af6fc084a1aa0463c1ccc975532
 *
 * Use Populate Anything's field settings to map a choice-based ACF field to a Gravity Forms choice-based field (e.g.
 * Drop Down, Radio Buttons, Checkboxes, etc). Then, use this snippet to automatically convert the ACF choices into
 * choices in the Gravity Forms field.
 */
add_filter( 'gppa_input_choices', function ( $choices, $field ) {

	if ( empty( $field->choices ) ) {
		return $choices;
	}

	$object = rgar( $choices[0], 'object' );
	if ( ! $object || ! is_a( $object, 'WP_Post' ) || $object->post_type !== 'acf-field' ) {
		return $choices;
	}

	$acf_field_config = maybe_unserialize( $object->post_content );
	if ( ! $acf_field_config || empty( $acf_field_config['choices'] ) ) {
		return $choices;
	}

	$new_choices = array();

	foreach ( $acf_field_config['choices'] as $value => $label ) {
		$new_choices[] = array(
			'value'      => $value,
			'text'       => $label,
			'isSelected' => false,
		);
	}

	return $new_choices;
}, 10, 2 );
