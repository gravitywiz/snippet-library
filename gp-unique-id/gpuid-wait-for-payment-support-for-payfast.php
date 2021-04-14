<?php
/**
 * Gravity Perks // GP Unique ID // Add Wait-for-payment Support for PayFast
 * https://gravitywiz.com/documentation/gp-unique-id/
 *  
 * This snippet adds support to capture payment made via PayFast before generating the Unique ID.
 *
 * Plugin Name:  GP Unique ID — Add Wait-for-payment Support for PayFast
 * Plugin URI:   https://gravitywiz.com/documentation/gp-unique-id/
 * Description:  Adds support to capture payment made via PayFast before generating the Unique ID.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
// 1 - Indicate that waiting for payments is enabled.
add_filter( 'gpui_wait_for_payment', '__return_true' );
 
// 2 - Fetch the applicable payment feed for your gateway.
add_filter( 'gpui_wait_for_payment_feed', function ( $return_feed, $form, $entry ) {
	$entry['id'] = null;
	$feed = gf_payfast()->get_payment_feed( $entry, $form );
	$submission_data = gf_payfast()->get_submission_data( $feed, $form, $entry );
	if ( $feed && ! empty( $submission_data['payment_amount'] ) ) {
		$return_feed = $feed;
	}
	return $return_feed;
}, 10, 3 );
 
// 3 - Call populate_field_value() with the $fulfilled flag set to true when the payment is completed.
// This example is how it works with the Gravity Forms PayPal add-on. When the payment is fulfilled,
// PayPal triggers the gform_paypal_fulfillment action. We bind to that action and call the
// populate_field_value() method to populate the unique ID when the payment is fulfilled.
add_action( 'gform_payfast_fulfillment', function ( $entry ) {
	$form = GFAPI::get_form( $entry['form_id'] );
	gp_unique_id_field()->populate_field_value( $entry, $form, true );
}, 9 );
