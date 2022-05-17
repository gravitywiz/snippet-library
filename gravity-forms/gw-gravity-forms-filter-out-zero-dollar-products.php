/**
 * Gravity Wiz // Gravity Forms // Filter Out $0.00 Products
 * http://gravitywiz.com/
 * 
 * Use this snippet to hide Products that have $0.00 Products
 * 
 */
add_filter( 'gform_product_info', 'gw_remove_empty_products', 10, 3 );
function gw_remove_empty_products( $product_info, $form, $lead ) {

	$products = array();
	
	foreach( $product_info['products'] as $field_id => $product ) {
		if( GFCommon::to_number( $product['price'] ) != 0 ) {
			$products[ $field_id ] = $product;
		}
	}

	$product_info['products'] = $products;

	return $product_info;
}
