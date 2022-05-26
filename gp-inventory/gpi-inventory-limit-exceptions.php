<?php
/**
 * Gravity Perks // Inventory // Inventory Limit Exceptions
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
// Update "123" to your form ID and "4" to your Inventory-enabled field ID.
add_filter( 'gpi_inventory_limit_advanced_123_4', function( $inventory_limit, $field ) {
	// Update "5" to the value of the scoped field whose value should impact the inventory limit.
	$value = rgpost( 'input_5' );
	switch ( $value ) {
		case '05/02/2022';
			$inventory_limit = 2;
			break;
		case '05/03/2022';
			$inventory_limit = 3;
			break;
	}
	return $inventory_limit;
}, 10, 2 );
