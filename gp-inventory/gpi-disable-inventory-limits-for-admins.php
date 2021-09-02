<?php
/**
 * Gravity Perks // GP Inventory // Disable Inventory Limits for Admins
 * http://gravitywiz.com/
 */
add_action( 'init', function () {
	if ( is_callable( 'gp_inventory' ) && current_user_can( 'administrator' ) ) {
		add_filter( 'gpi_remove_choices', '__return_false' );
		add_filter( 'gpi_disable_choices', '__return_false' );

		$inventory_types = array(
			gp_inventory_type_simple(),
			gp_inventory_type_advanced(),
			gp_inventory_type_choices(),
		);

		foreach ( $inventory_types as $inventory_type ) {
			remove_filter( 'gform_validation', array( $inventory_type, 'validation' ) );
			remove_filter( 'gform_pre_render', array( $inventory_type, 'maybe_lockout' ), 11 );
		}
	}
}, 17 ); // GP Inventory types become available after priority 16 of init.
