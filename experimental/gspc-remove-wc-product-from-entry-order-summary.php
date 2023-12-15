<?php
/**
 * Gravity Shop // Product Configurator // Remove WC Product from Entry Order Summary
 * https://gravitywiz.com/documentation/gs-product-configurator/
 */
add_filter( 'init', function() {
	if ( is_callable( 'gs_product_configurator' ) ) {
		remove_filter( 'gppa_ajax_form_pre_render', [ gs_product_configurator()->wc_product_form_display, 'inject_base_price_product_field_gppa_ajax' ] );
		remove_filter( 'gform_product_info', [ gs_product_configurator()->wc_product_form_display, 'inject_base_price_into_product_info' ]);
	}
}, 16 );
