<?php
/**
 * Gravity Perks // Populate Anything // Live Merge Tags: Numbers Only Modifier
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Experimental Snippet 🧪
 */
add_filter( 'gppa_live_merge_tag_value', function( $value, $merge_tag, $form, $field_id, $entry_values ) {
	if ( strpos( $merge_tag, ':numbersonly' ) !== false && preg_match( '/([0-9]+)/', $value, $matches ) ) {
		$value = $matches[0];
	}
	return $value;
}, 10, 5 );
