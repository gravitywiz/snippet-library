<?php
/**
 * Gravity Wiz // Gravity Forms // Populate the Current Quarter
 * https://gravitywiz.com/snippet-library/
 */
add_filter( 'gform_field_value_current_quarter', function() {

	$month   = gmdate( 'n' );
	$quarter = ceil( $month / 3 );

	return $quarter;
} );
