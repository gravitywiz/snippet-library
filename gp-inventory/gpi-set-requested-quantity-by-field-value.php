<?php
/**
 * Gravity Perks // Inventory // Set Requested Quantity by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
add_filter( 'gpi_requested_quantity_123_4', function( $requested_quantity ) {
	return rgpost( 'input_3' );
} );
