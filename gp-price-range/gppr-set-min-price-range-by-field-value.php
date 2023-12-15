<?php
/**
 * Gravity Perks // GP Price Range // Modify The Minimum Price Range Based On A Field Value
 * https://gravitywiz.com/documentation/gravity-forms-price-range/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppr_price_range_min_123_4', function( $min ) {

	// Update "5" to the field ID whose value should be used as the minimum price range.
	$source_field_id = 5;

	return $_POST[ 'input_' . $source_field_id ];
} );
