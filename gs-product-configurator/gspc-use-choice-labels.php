<?php
/**
 * Gravity Wiz // GS Product Configurator // Display Choice Labels for Product Add-ons
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * By default, Gravity Forms will display the value of a choice after it has been submitted. Install this snippet
 * to display the label of choices in the WooCommerce Cart, Checkout, Order, etc.
 *
 * Worth noting, if you use GP Populate Anything, fields with dynamically populated choices will already display the
 * label of the choice rather than the value as Populate Anything relies on the use of IDs and other unique identifiers
 * of objects.
 *
 * Instructions: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gspc_addon_display_value', function( $display_value, $field, $entry, $form ) {
	return gspc_get_choice_label( $display_value, $field );
}, 10, 4 );

function gspc_get_choice_label( $value, $field ) {
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

// Choice-based Product fields must be handled uniquely.
add_filter( 'gspc_addons', function( $addons, $object, $form, $entry ) {
	$products = GFCommon::get_product_fields( $form, $entry );
	foreach ( $addons as &$addon ) {
		$product_field = GFAPI::get_field( $form, $addon['field_id'] );
		$addon_product = rgars( $products, "products/{$addon['field_id']}" );
		if ( ! $addon_product ) {
			continue;
		}
		$choice_label = gspc_get_choice_label( $addon_product['name'], $product_field );
		if ( $choice_label !== $addon_product['name'] ) {
			$addon['value'] = str_replace( $addon_product['name'], $choice_label, $addon['value'] );
		}
	}
	return $addons;
}, 10, 4 );
