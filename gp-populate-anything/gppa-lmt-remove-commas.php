<?php
/**
 * Gravity Perks // Populate Anything // Remove Commas From Live Merge Tag
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Remove commas from the value copied with a Live Merge Tag
 * This is useful when copying values over 1,000 from a Number field to a Quantity field.
 */
// Update "123" to your form ID; and "4" to the field ID you are copying from.
add_filter( 'gppa_live_merge_tag_value_123_4', function( $value ) {
	return str_replace( ',', '', $value );
} );
