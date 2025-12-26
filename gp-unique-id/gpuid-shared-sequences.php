<?php
/**
 * Gravity Perks // Unique ID // Shared Sequences
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Share sequences between sequential Unique ID fields; works with fields on different forms as well.
 *
 * To use the snippet, replace the numbers in the array below to match your form IDs and field IDs.
 * For example, if Form A has an ID of 123 and its Unique ID field's ID is 1 and Form B has an ID of
 * 456 and its Unique ID field's ID is 2, then you would set up the group like so:
 *
 * ```php
 * $groups = array(
 *     array(
 *         // Form ID => Field ID
 *         123 => 1,
 *         456 => 2,
 *     )
 * );
 * ```
 */
add_filter( 'gpui_unique_id_attributes', function ( $atts, $form_id, $field_id ) {

	$groups = array(
		array(
			123 => 4,
			456 => 7,
		),
		array(
			124 => 5,
			125 => 6,
		),
	);

	if ( $atts['type'] !== 'sequential' ) {
		return $atts;
	}

	$group_number = false;

	foreach ( $groups as $index => $group ) {
		foreach ( $group as $_form_id => $_field_id ) {
			if ( $_form_id == $form_id && $_field_id == $field_id ) {
				$group_number = $index + 1;
				break;
			}
		}
	}

	if ( $group_number === false ) {
		return $atts;
	}

	$atts['starting_number'] = 1;

	$atts['slug'] = array(
		'form_id'  => 0,
		'field_id' => 0,
		'slug'     => 'shared-sequence-' . $group_number,
	);

	return $atts;
}, 10, 3 );


