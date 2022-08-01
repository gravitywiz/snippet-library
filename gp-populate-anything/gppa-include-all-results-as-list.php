<?php
/**
 * Gravity Perks // Populate Anything // Include All Query Results In Value As Comma-delimited List
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_get_input_values_FORMID_FIELDID', function ( $value, $field, $template, $objects ) {
	$processed_values = array();
	foreach ( $objects as $object ) {
		$processed_values[] = gp_populate_anything()->process_template( $field, $template, $object, 'values', $objects );
	}
	return implode( ',', $processed_values );
}, 10, 4 );
