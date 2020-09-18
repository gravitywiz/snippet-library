<?php
/**
 * Gravity Perks // GP eCommerce Fields // Add Custom Total to Order Summary
 * http://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 */
// Update "123" with your form ID.
add_filter( 'gpecf_order_summary_123', function( $order_summary ) {

	$order_summary['total'] = array_merge( array( array(
		'name'     => 'Recurring Total',
		'price'    => '$400',
		'quantity' => 1,
		'class'    => 'total'
	) ), $order_summary['total'] );

	return $order_summary;
} );