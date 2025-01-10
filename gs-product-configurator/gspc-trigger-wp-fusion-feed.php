<?php
/**
 * Gravity Shop // GS Product Configurator // Trigger WP Fusion feed when payment is delayed
 *
 * WP Fusion does not register its Gravity Forms add-on using `GFAddOn::register`, nor does it
 * run `add_delayed_payment_support` which means the feed won't run when the payment is delayed.
 *
 * This snippet works around that.
 */
add_action('plugins_loaded', function() {
	if ( function_exists( 'wp_fusion' ) ) {
		add_action( 'gform_trigger_payment_delayed_feeds', function ( $transaction_id, $payment_feed, $entry, $form ) {
			if ( ! property_exists( wp_fusion(), 'integrations' ) || ! property_exists( wp_fusion()->integrations, 'gravity-forms' ) ) {
				return;
			}

			$addon = wp_fusion()->integrations->{'gravity-forms'};

			add_filter( 'gspc_delay_feed_processing', '__return_false', 9998 );
			$addon->maybe_process_feed( $entry, $form );
			remove_filter( 'gspc_delay_feed_processing', '__return_false', 9998 );
		}, 10, 4 );
	}
});
