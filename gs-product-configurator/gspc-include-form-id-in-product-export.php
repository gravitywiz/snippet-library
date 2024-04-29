<?php
/**
 * Gravity Shop // Product Configurator // Include Form ID in WooCommerce Product Exports
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 */
add_filter( 'woocommerce_product_export_row_data', function( $row, $product ) {
	$form_id = gspc_get_product_form_id( $product );
	if ( $form_id ) {
		$row['gspc_form_id'] = $form_id;
	}
	return $row;
}, 10, 2 );

add_filter( 'woocommerce_product_export_column_names', function( $column_names ) {
	$column_names['gspc_form_id'] = __( 'GSPC Form ID' );
	return $column_names;
} );
