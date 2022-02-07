<?php
/**
 * Gravity Perks // eCommerce Fields // Consolidate Separate Discount Line Items into a Single Discounts Line Item
 * http://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * Default:    https://gwiz.io/2IiPALf
 * w/ Snippet: https://gwiz.io/2Itfyfo
 */
// Update "123" to your form ID - or - remove "_123" to apply to all forms.
add_filter( 'gpecf_order_summary_123', function( $summary ) {

	if ( empty( $summary['discounts'] ) ) {
		return $summary;
	}

	$consolidated_discount          = $summary['discounts'][0];
	$consolidated_discount['name']  = 'Discounts';
	$consolidated_discount['price'] = 0;

	foreach ( $summary['discounts'] as $discount ) {
		$consolidated_discount['price'] += $discount['price'];
	}

	$summary['discounts'] = array( $consolidated_discount );

	return $summary;
} );
