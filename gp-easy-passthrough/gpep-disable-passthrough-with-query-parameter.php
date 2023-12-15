<?php
/**
 * Gravity Perks // Easy Passthrough // Disabling Passthrough With Query Parameter
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 */
add_filter( 'gpep_field_values', function( $field_values, $form_id ) {
	// If passthrough=0 is in the query parameters, return an empty array.
	if ( '0' == rgget( 'passthrough' ) ) {
		return array();
	}
	return $field_values;
}, 10, 2 );
