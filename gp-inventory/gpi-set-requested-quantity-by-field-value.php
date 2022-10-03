<?php
/**
 * Gravity Perks // Inventory // Set Requested Inventory by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
// Update "123" to your form ID and "4" to your Inventory-enabled field.
add_filter( 'gpi_requested_quantity_123_4', function( $requested_quantity ) {
	// Update "5" to the ID of the field that should be used to specify the quantity.
	return rgpost( 'input_5' );
} );
