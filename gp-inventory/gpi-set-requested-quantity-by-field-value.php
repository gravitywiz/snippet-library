<?php
/**
 * Gravity Perks // Inventory // Set Requested Inventory by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
// Update "123" to your form ID and "4" to your Inventory-enabled field.
add_filter( 'gpi_quantity_input_ids_123_4', function( $input_ids, $field ) {
	// Update "5" to the ID of the field that should be used to specify the quantity.
	return array( 5 );
}, 10, 2 );
