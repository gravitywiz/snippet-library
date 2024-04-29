<?php
/**
 * Gravity Shop // GS Product Configurator // Trigger feeds when payment is on hold.
 *
 * By default, feeds are only triggered when the payment is processing or complete.
 *
 * Instructions:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Update `$payment_methods`, `$forms`, and `$do_not_process_feeds` accordingly.
 */

add_action( 'gform_post_payment_action', function( $entry, $action ) {
	if ( ! is_callable( 'gs_product_configurator' ) ) {
		return;
	}

	/**
	 * Update this with the WooCommerce payment methods
	 * for which you want to trigger feeds when the order is on hold.
	 * Else, feeds will be triggered for all payment methods.
	 *
	 * Example:
	 *     $payment_methods = array( 'cod', 'bacs' );
	 *     'cheque' = Check payments
	 *     'bacs'   = Bank transfer (BACS) payments
	 *     'cod'    = Cash on delivery
	 */
	$payment_methods = array();

	/**
	 * Update this with the IDs of the forms
	 * for which the feeds should be processed. Else,
	 * feeds will be processed for all forms applicable.
	 * E.g. $forms = array( 123, 456 );
	 */
	$forms = array();

	if ( ! empty( $forms ) && ! in_array( (int) $entry['form_id'], $forms, true ) ) {
		return;
	}

	if ( ! isset( $action['transaction_id'] ) ) {
		return;
	}

	$wc_order = wc_get_order( absint( $action['transaction_id'] ) );

	if ( ! empty( $payment_methods )
		&& ! in_array( $wc_order->get_payment_method(), $payment_methods, true ) ) {
		return;
	}

	if ( ! isset( $action['type'] )
		|| 'add_pending_payment' !== $action['type']
		|| 'on-hold' !== $wc_order->get_status() ) {
		return;
	}

	/** @var GSPCFeed|false $payment_feed */
	$payment_feed = gs_product_configurator()->get_payment_feed( $entry );

	/**
	 * @var GFForm|null $form
	 */
	$form = GFAPI::get_form( $entry['form_id'] );

	if ( ! $payment_feed || ! $form ) {
		return;
	}

	/**
	 * Update this with the slug(s) of the feed add-on(s)
	 * that you DON'T want processed e.g. 'gravityformsuserregistration'
	 * for GF User Registration. Else, ALL feeds will be processed!!!
	 */
	$do_not_process_feeds = array();

	// Ensure GSPC isn't mistakenly included.
	$do_not_process_feeds = array_filter( $do_not_process_feeds, function ( $feed ) {
		return 'gs-product-configurator' !== $feed;
	} );

	if ( ! empty( $do_not_process_feeds ) ) {
		foreach ( $do_not_process_feeds as $feed ) {
			add_filter( "gform_{$feed}_pre_process_feeds", '__return_empty_array' );
		}
	}

	gs_product_configurator()->trigger_payment_delayed_feeds( $action['transaction_id'], $payment_feed, $entry, $form );

	if ( ! empty( $do_not_process_feeds ) ) {
		foreach ( $do_not_process_feeds as $feed ) {
			remove_filter( "gform_{$feed}_pre_process_feeds", '__return_empty_array' );
		}
	}
}, 10, 2 );
