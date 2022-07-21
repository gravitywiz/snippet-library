<?php
/**
 * Gravity Perks // Inventory // Set Inventory Limit by Custom Field
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * This filter sets the inventory of an Advanced resource dynamically based on the
 * inventory custom field of the post/page on which the form is embedded.
 */
add_filter( 'gpi_inventory_limit_advanced', function() {
	// Update 'inventory' to the custom field meta key.
	return get_post_meta( get_queried_object_id(), 'inventory', true );
} );
