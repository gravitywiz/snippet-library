<?php
/**
 * Gravity Perks // GP Conditional Pricing // Fuzzy Comparison for operator "is" (PHP)
 * http://gravitywiz.com/documentation/gravity-forms-conditional-pricing/
 *
 * This snippet requires the JS counterpart gpcp-fuzzy-comparison.js
 */
add_filter( 'gform_is_value_match', function ( $is_match, $field_value, $target_value, $operation, $source_field, $rule ) {
	if ( $rule['operator'] !== 'is' ) {
		return $is_match;
	}
	return fuzzy_match( $field_value, trim( $value ) );
}, 10, 6 );

function normalize_string( $str ) {
	$str = iconv( 'UTF-8', 'ASCII//TRANSLIT', $str );
	return strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', $str ) );
}

function fuzzy_match( $input, $target, $threshold = 1 ) {
	$normalized_input  = normalize_string( $input );
	$normalized_target = normalize_string($target);
	$distance          = levenshtein( $normalized_input, $normalized_target );
	return $distance <= $threshold;
}
