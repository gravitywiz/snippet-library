<?php
/**
 * Gravity Wiz // WC GF Product Add-ons // Display Choice Labels in Card & Order
 * https://gravitywiz.com/
 */
add_filter( 'woocommerce_gforms_get_item_data', function( $data, $field ) {
	$data['display'] = gw_wcgfpa_get_choice_label( $data['display'], $field );
	return $data;
}, 10, 2);

add_filter( 'woocommerce_gforms_order_meta_value', function( $display_value, $field ) {
	return gw_wcgfpa_get_choice_label( $display_value, $field );
}, 10, 2 );

function gw_wcgfpa_get_choice_label( $value, $field ) {
	if ( ! empty( $field->choices ) ) {
		foreach ( $field->choices as $choice ) {
			if ( $choice['value'] == $value ) {
				$value = $choice['text'];
				break;
			}
		}
	}
	 return $value;
}
