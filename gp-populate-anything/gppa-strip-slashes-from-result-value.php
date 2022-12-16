<?php
/**
 * Gravity Perks // Populate Anything // Strip slashes from result values
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Replace "123" with your form ID and "4" with your field ID.
add_filter( 'gppa_get_input_values_123_4', function ( $value, $field, $template, $objects ) {
	return stripslashes( $value );
}, 10, 4 );
