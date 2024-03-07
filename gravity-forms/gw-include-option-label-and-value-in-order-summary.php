<?php
/**
 * Gravity Wiz // Gravity Forms // Include Option Label & Value in Order Summary
 * https://gravitywiz.com/
 *
 * By default, Gravity Forms only includes select options' labels or values depending on the context. Use this snippet
 * to include both the label and value wherever the order summary is displayed (e.g. Entry Detail, {all_fields}, etc).
 */
add_filter( 'gform_product_info', function( $product_info, $form ) {
	foreach ( $product_info['products'] as &$product ) {
		foreach ( $product['options'] as &$option ) {
			$field = GFAPI::get_field( $form, $option['id'] );
			foreach ( $field->choices as $choice ) {
				if ( $choice['text'] !== $choice['value'] && in_array( $option['option_name'], array( $choice['text'], $choice['value'] ) ) ) {
					$option['option_label'] = sprintf( '%s: %s, %s', $field->label, $choice['text'], $choice['value'] );
				}
			}
		}
	}
	return $product_info;
}, 10, 2 );
