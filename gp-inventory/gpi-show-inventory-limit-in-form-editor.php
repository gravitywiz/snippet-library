<?php
/**
 * Gravity Perks // Inventory // Show Inventory Limit in Form Editor
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * By default the current inventory is shown in the form editor.
 * Use this filter to show the inventory limit.
 */
add_filter( 'gpi_always_show_inventory_limit_in_editor', '__return_true' );
