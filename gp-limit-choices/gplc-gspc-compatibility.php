<?php
/**
 * Gravity Perks // GP Limit Choices // GSPC compatibility
 *
 * This is snippet will ensure that validation is checked again before an order is created.
 * https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 */

add_action( 'woocommerce_checkout_create_order', function( $order ) {
	if ( ! function_exists( 'gp_limit_choices' ) || ! function_exists( 'gs_product_configurator' ) ) {
		return;
	}

	$items = $order->get_items();

	foreach ( $items as $item ) {
		$gspc_order_item = new GS_Product_Configurator\WC_Order_Item( $item );
		$entries         = $gspc_order_item->get_entries();

		foreach ( $entries as $entry ) {
			add_filter( 'gwlc_selected_values', function( $value, $field ) use ( $entry ) {
				return GFFormsModel::get_lead_field_value( $entry, $field );
			}, 10, 2 );
			add_filter( 'gplc_requested_count', function( $count, $field ) use ( $entry ) {
				return rgar( $entry, gp_limit_choices()->get_product_quantity_input_id( $field ) );
			}, 10, 2 );

			$form = GFAPI::get_form( $entry['form_id'] );

			if ( gp_limit_choices()->has_validation_error( $form ) ) {
				$message = field_validation_message( $form );
				throw new \Exception( $message );
			}
		}
	}

}, 11 );

public function field_validation_message( $form ) {
	foreach ( $form['fields'] as $field ) {
		if ( rgar( $field, 'validation_message' ) ) {
			return $field['validation_message'];
		}
	}
	return null;
}
