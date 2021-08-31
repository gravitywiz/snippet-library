<?php
/**
 * Gravity Perks // Populate Anything // Display Multiple Items from Repeater Row
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to your GPPA-populated field ID.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {
	foreach ( $choices as &$choice ) {
		$index = explode( '_', $choice['object']['meta_key'] )[1];
		$choice['text'] .= ' (' . get_post_meta( $choice['object']['post_id'], "plats_{$index}_plats_och_datum_plats", true ) . ')';
	}
	return $choices;
}, 10, 3 );
