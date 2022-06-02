<?php
/**
 * Gravity Perks // Inventory // Grant Additional Inventory by Role
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
add_filter( 'gpi_inventory_limit_advanced_123_4', function( $inventory_limit, $field ) {
	// Update "administrator" to whatever role you would like to grant additional inventory to.
	if ( current_user_can( 'administrator' ) ) {
		// Update "5" to the amount of additional inventory that should be granted to the specified role.
		$inventory_limit += 50;
	}
	return $inventory_limit;
}, 10, 2 );
