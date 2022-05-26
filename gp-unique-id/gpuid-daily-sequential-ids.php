<?php
/**
 * GP Unique ID // Gravity Perks // Creates Daily Sequential IDs
 * http://gravitywiz.com/
 *
 * Make the sequence for a given Unique ID field specific to the current date. The sequence will automatically
 * reset for the next day.
 */
// Update "123" to your form ID and "4" to your Unique ID field.
add_filter( 'gpui_unique_id_attributes_123_4', function( $atts ) {
	$atts['form_id'] = (int) date( 'Ymd' );
	return $atts;
} );
