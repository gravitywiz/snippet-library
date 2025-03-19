<?php
/**
 * Gravity Shop // Product Configurator // Clear stale Entries on load
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Although stale entries are cleared via the `woocommerce_cleanup_sessions` cron schedule that runs twice daily,
 * this snippet will clear the stale entries on site load.
 */

add_action( 'woocommerce_set_cart_cookies', function( $set ) {
	if ( ! $set && class_exists( 'GS_Product_Configurator\Entry_Lifecycle' ) ) {
		$entry_lifecycle = new GS_Product_Configurator\Entry_Lifecycle();
		$entry_lifecycle->cleanup_stale_entries();
	}
} );
