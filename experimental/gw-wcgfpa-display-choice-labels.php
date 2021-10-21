<?php
/**
 * Gravity Wiz // WC GF Product Add-ons // Display Choice Labels in Cart
 * https://gravitywiz.com/
 */
add_filter( 'woocommerce_gforms_order_meta_value', function( $value, $field ) {

	if ( ! empty( $field->choices ) ) {
		foreach ( $field->choices as $choice ) {
			if ( $choice['value'] == $value ) {
				$value = $choice['text'];
			}
		}
	}
	
	return $value;
}, 10, 2 );
