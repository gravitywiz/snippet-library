<?php
/**
 * Gravity Perks // Inventory // Exhausted Inventory Threshold
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Mark field as out of stock when a given number of choices with exhausted inventory is reached.
 * For example, if you had 5 choices and wanted to disable the field after 3 choices' inventory
 * was exhausted, this snippet would do the trick.
 *
 * Instructions
 *
 * 1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 * 2. Follow the inline instructions to configure the snippet for your form.
 */
// Update "123" to your form ID and "4" to your Inventory-enabled field ID.
add_filter( 'gpi_is_in_stock_123_4', function( $is_in_stock, $field, $stock ) {
	$disabled_count = 0;
	foreach ( $field->choices as $choice ) {
		if ( rgar( $choice, 'isDisabled' ) ) {
			$disabled_count++;
		}
	}
	// Update "3" to the number of choices with exhausted inventory required to mark the field as out of stock.
	if ( $disabled_count >= 3 ) {
		$is_in_stock = false;
	}
	return $is_in_stock;
}, 10, 3 );
