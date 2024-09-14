<?php
/**
 * Gravity Shop // GS Product Configurator // Remove Zero Dollar WooCommerce Products from Summary.
 *
 * Instructions: https://www.loom.com/share/384805955a184bada7034bea6d9de857
 */
if ( is_callable( 'gs_product_configurator' ) ) :
	add_filter( 'gform_product_info', function ( $product_info, $form, $entry ) {
		if ( isset( $product_info['products'][ \GS_Product_Configurator\WC_Product_Form_Display::BASE_PRICE_PRODUCT_FIELD_ID ] ) ) {
			$price         = $product_info['products'][\GS_Product_Configurator\WC_Product_Form_Display::BASE_PRICE_PRODUCT_FIELD_ID]['price'];
			$numeric_price = (float) preg_replace( '/[^\d.]/', '', $price );

			if ( $numeric_price == 0.0 ) {
				unset( $product_info['products'][ \GS_Product_Configurator\WC_Product_Form_Display::BASE_PRICE_PRODUCT_FIELD_ID ] );
			}
		}

		return $product_info;
	}, 16, 3 );
endif;
