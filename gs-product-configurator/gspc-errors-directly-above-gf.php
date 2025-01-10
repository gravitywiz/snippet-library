<?php
/**
 * Gravity Shop // GS Product Configurator // Move validation errors to be directly above product Gravity Form.
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * By default, Gravity Shop Product Configurator will display validation errors at the very top of the form used
 * to configure a product. This means it would be above any variations and other controls provided by WooCommerce
 * and other WooCommerce extensions.
 *
 * Instructions:
 *   Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gform_get_form_filter', function( $markup, $form ) {
	if ( ! function_exists( 'gs_product_configurator' ) || ! gs_product_configurator()->should_enqueue_frontend( $form ) ) {
		return $markup;
	}

	$pattern  = '/<div class="gform_validation_errors" ([\s\S]+?)<\/div>/';
	$pattern2 = '/(<div class=(["\'])gform[-_]body)/';
	if ( preg_match( $pattern, $markup, $matches ) ) {
		$markup = preg_replace( $pattern, '', $markup );
		$markup = preg_replace( $pattern2, $matches[0] . '$1', $markup );
	}
	return $markup;
}, 10, 2 );
