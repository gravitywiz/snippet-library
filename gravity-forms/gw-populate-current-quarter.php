<?php
/**
 * Gravity Wiz // Gravity Forms // Populate the Current Quarter
 * https://gravitywiz.com/snippet-library/
 *
 * Instruction video: https://www.loom.com/share/78e7add08c7042a4ad02b2682a3a8ee9
 *
 *
 */
add_filter( 'gform_field_value_current_quarter', function() {

	$month   = gmdate( 'n' );
	$quarter = ceil( $month / 3 );

	return $quarter;
} );
