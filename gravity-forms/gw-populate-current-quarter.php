<?php
/**
 * Gravity Wiz // Gravity Forms // Populate the Current Quarter
 * https://gravitywiz.com/snippet-library/
 *
 * Instruction Video: https://www.loom.com/share/a8355c7ab85c42e88130a5eda11a1f81
 */
add_filter( 'gform_field_value_current_quarter', function() {

	$month   = gmdate( 'n' );
	$quarter = ceil( $month / 3 );

	return $quarter;
} );
