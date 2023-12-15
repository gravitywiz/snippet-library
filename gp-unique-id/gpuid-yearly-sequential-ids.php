<?php
/**
 * GP Unique ID // Gravity Perks // Creates Yearly Sequential IDs
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Make the sequence for a given Unique ID field specific to the current year. The sequence
 * will automatically reset each year.
 */
// Update "123" to your form ID and "4" to your Unique ID field.
add_filter( 'gpui_unique_id_attributes_123_4', function( $atts ) {
	$atts['form_id'] = (int) gmdate( 'Y' ) . '0000';
	return $atts;
} );
