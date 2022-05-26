<?php
/**
 * Gravity Perks // Inventory // Limit by Scoped Value
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
add_filter( 'gpi_inventory_limit_advanced_156_3', function( $inventory_limit, $field ) {
	$date = rgpost( 'input_1' );
	switch ( $date ) {
		case '05/02/2022';
			$inventory_limit = 2;
			break;
		case '05/03/2022';
			$inventory_limit = 3;
			break;
	}
	return $inventory_limit;
}, 10, 2 );
