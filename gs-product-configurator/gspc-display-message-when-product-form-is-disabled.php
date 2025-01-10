<?php
/**
 * Gravity Shop // Product Configurator // Display Message When Product Form is Disabled
 * * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Instructions:
 *
 * 1. Install this snippet per https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 */
add_action( 'woocommerce_single_product_summary', function() {
	global $product;

	if ( ! function_exists( 'gs_product_configurator' ) ) {
		return;
	}

	if ( ! gs_product_configurator()->wc_product_display->is_product_form_disabled( $product ) ) {
		return;
	}

	echo '<p>' . esc_html__( 'Sorry. This form is no longer available.', 'gravityforms' ) . '</p>';
} );
