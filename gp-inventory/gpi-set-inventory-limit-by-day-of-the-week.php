<?php
/**
 * Gravity Perks // Inventory // Inventory Limit by Day of the Week
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Set different inventory amounts depending on the day of the week selected in a scoped Date field. This snippet is
 * specifically configured to increase the inventory limit for weekend days.
 */
// Update "123" to your form ID and "4" to your Inventory-enabled field ID.
add_filter( 'gpi_inventory_limit_advanced_123_4', function( $inventory_limit, $field ) {

	// Update "5" to the value of the scoped field whose value should impact the inventory limit.
	$value = rgpost( 'input_5' );
	if ( empty( $value ) ) {
		return $inventory_limit;
	}

	// Update "m/d/Y" to your date format.
	$date = DateTime::createFromFormat( 'm/d/Y', $value );

	$day_of_week = (int) $date->format( 'w' );
	$is_weekend  = $day_of_week === 0 || $day_of_week === 6;

	if ( $is_weekend ) {
		$inventory_limit = 10;
	}

	return $inventory_limit;
}, 10, 2 );
