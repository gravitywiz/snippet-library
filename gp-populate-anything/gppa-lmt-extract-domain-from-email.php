<?php
/**
 * Gravity Perks // Populate Anything // Extract Domain from Email Address
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID; and "4" to your Email field ID.
add_filter( 'gppa_live_merge_tag_value_123_4', function( $value ) {
	$bits = explode( '@', $value );
	return array_pop( $bits );
} );
