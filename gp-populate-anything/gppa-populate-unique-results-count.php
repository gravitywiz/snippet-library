<?php
/**
 * Gravity Perks // Populate Anything // Populate Unique Results Count
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_get_input_values_123_4', function( $values, $field, $template, $objects ) {
	return count( $objects );
}, 10, 4 );
