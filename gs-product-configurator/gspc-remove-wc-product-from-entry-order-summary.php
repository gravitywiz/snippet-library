<?php
/**
 * Gravity Shop // Product Configurator // Remove WC Product from Entry Order Summary
 * https://gravitywiz.com/documentation/gs-product-configurator/
 *
 * Experimental Snippet 🧪
 */
add_filter( 'init', function() {
	if ( is_callable( 'gs_product_configurator' ) ) {
		remove_filter( 'gppa_ajax_form_pre_render', array( gs_product_configurator()->wc_product_form_display, 'inject_base_price_product_field_gppa_ajax' ) );
		remove_filter( 'gform_product_info', array( gs_product_configurator()->wc_product_form_display, 'inject_base_price_into_product_info' ) );
		remove_filter( 'gform_pre_render', array( gs_product_configurator()->wc_product_form_display, 'inject_base_price_product_field' ), 5 );
	}
}, 16 );
