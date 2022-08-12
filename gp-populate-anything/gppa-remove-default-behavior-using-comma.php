<?php
/**
 * Gravity Perks // GP Populate Anything // Remove Default Behavior of Using a Comma as a Delimiter
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_array_value_to_text', function( $text_value, $array_value ) {
	return json_encode( $array_value );
}, 11, 2 );
