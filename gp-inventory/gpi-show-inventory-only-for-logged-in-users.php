<?php
/**
 * Gravity Perks // Inventory // Show Inventory Only For Logged In users
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 * 
 * Instruction Video: https://www.loom.com/share/2d9843302a54456987ed2c66a83612ba
 *
 * By default the inventory available is shown for everyone, if enabled.
 * Use this filter to show the inventory available only for logged in users.
 */
add_filter( 'gpi_inventory_available_message', function( $message ) {
	if ( is_user_logged_in() ) {
		return $message;
	}
	return '';
} );
