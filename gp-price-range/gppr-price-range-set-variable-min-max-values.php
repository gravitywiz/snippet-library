<?php
/**
 * Gravity Perks // GP Price Range // Set a Variable Min/Max Range Based On A Field Value
 * https://gravitywiz.com/documentation/gravity-forms-price-range/
 *
 * Take a base value and calculate a minimum and maximum range based on that value.
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppr_price_range_min_123_4', 'gw_variable_price_range' );
add_filter( 'gppr_price_range_max_123_4', 'gw_variable_price_range' );
function gw_variable_price_range( $value ) {
	// Update "5" to the field ID whose value should be used as the minimum price range.
	$source_field_id = 5;
	$base_value      = (int) rgpost( 'input_' . $source_field_id );
	$is_min          = strpos( current_filter(), 'min' ) !== false;
	$range           = 50;

	if ( $is_min ) {
		$value = $base_value - $range;
	} else {
		$value = $base_value + $range;
	}

	return $value;
}
