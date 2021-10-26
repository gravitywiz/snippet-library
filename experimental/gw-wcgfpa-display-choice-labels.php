<?php
/**
 * Gravity Wiz // WC GF Product Add-ons // Display Choice Labels in Cart
 * https://gravitywiz.com/
 */
add_filter( 'woocommerce_gforms_get_item_data', function( $data, $field, $entry, $form ) {
	if ( ! empty( $field->choices ) ) {
		foreach ( $field->choices as $choice ) {
			if ( $choice['value'] == $data['display'] ) {
				$data['display'] = $choice['text'];
			}
		}
	}
	return $data;
}, 10, 4 );
