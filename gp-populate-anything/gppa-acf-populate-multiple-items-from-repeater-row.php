<?php
/**
 * Gravity Perks // Populate Anything // Display Multiple Items from Repeater Row
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Experimental Snippet 🧪
 */
// Update "123" to your form ID and "4" to your GPPA-populated field ID.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {
	$filtered = array();
	foreach ( $choices as $choice ) {
		$index        = explode( '_', $choice['object']['meta_key'] )[1];
		$is_available = (bool) get_post_meta( $choice['object']['post_id'], substr( $choice['object']['meta_key'], 0, -5 ) . 'tillganglighet', true );
		if ( $is_available ) {
			$title           = get_post_meta( $choice['object']['post_id'], "plats_{$index}_plats_och_datum_plats", true );
			$choice['text'] .= sprintf( ' (%s)', $title );
			$filtered[]      = $choice;
		}
	}
	return $filtered;
}, 10, 3 );
