<?php
/**
 * Gravity Perks // eCommerce Fields // Remove Field Label from Option Label in Order Summary
 * https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * Instruction Video: https://www.loom.com/share/87f6010d6e624e4c9433699cabaf846b
 *
 * By default, Gravity Forms displays options in order summary like so:
 * Field Label: Option Label
 *
 * With this snippet in place, options will be displayed as:
 * Option Label
 */
add_filter( 'gform_product_info', function( $product_info ) {
	foreach ( $product_info['products'] as &$product ) {
		if ( ! empty( $product['options'] ) ) {
			foreach ( $product['options'] as &$option ) {
				$option['option_label'] = $option['option_name'];
			}
		}
	}
	return $product_info;
} );
