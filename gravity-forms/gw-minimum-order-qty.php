<?php
/**
 * Gravity Wiz // Gravity Forms // Set a Minimum Order Quantity
 * https://gravitywiz.com/set-a-minimum-order-quantity/
 */
add_filter('gform_validation_123', function( $result ) {

	$result = gw_validate_minimum_quantity( $result, array(
		'min_qty'         => 20,
		'min_qty_message' => 'Customized messaged for this group.',
		'min_qty_fields'  => array( 1, 2, 3 ),
	) );

	$result = gw_validate_minimum_quantity( $result, array(
		'min_qty'        => 15,
		'min_qty_fields' => array( 4, 5, 6 ),
	) );

	return $result;
} );

function gw_validate_minimum_quantity( $result, $args = array() ) {

	$args = wp_parse_args( $args, array(
		'min_qty'               => 1,
		'min_qty_message'       => 'You must order a minimum of %1$d tickets. Order %2$d more tickets to complete your order.',
		'min_qty_fields'        => array(),
		'validate_empty_fields' => false,
	) );

	$min_qty         = $args['min_qty'];
	$min_qty_message = $args['min_qty_message'];
	$min_qty_fields  = $args['min_qty_fields'];

	/* no need to edit below this line */

	$form              = $result['form'];
	$quantity          = 0;
	$qty_fields        = array();
	$has_entered_value = false;

	foreach ( $form['fields'] as &$field ) {

		// if $min_qty_fields specified, make sure only applicable quantity fields are totaled
		if ( ! empty( $min_qty_fields ) && ! in_array( $field['id'], $min_qty_fields ) ) {
			continue;
		}

		if ( in_array( RGFormsModel::get_input_type( $field ), array( 'singleproduct', 'calculation' ) ) ) {

			// check if product field has separate quantity field, if so skip
			if ( sizeof( GFCommon::get_product_fields_by_type( $form, array( 'quantity' ), $field['id'] ) ) > 0 ) {
				continue;
			}

			$value = rgpost( "input_{$field['id']}_3" );
			if ( ! rgblank( $value ) ) {
				$has_entered_value = true;
			}

			$quantity    += floatval( GFCommon::clean_number( $value ) );
			$qty_fields[] =& $field;

		} elseif ( $field['type'] == 'quantity' ) {

			$value = rgpost( "input_{$field['id']}" );
			if ( ! rgblank( $value ) ) {
				$has_entered_value = true;
			}

			$quantity += floatval( GFCommon::clean_number( $value ) );
			if ( ! rgblank( $value ) || $args['validate_empty_fields'] ) {
				$qty_fields[] =& $field;
			}
		}
	}

	if ( ! $has_entered_value || $quantity >= $min_qty ) {
		return $result;
	}

	for ( $i = 0; $i < count( $qty_fields ); $i++ ) {
		$qty_fields[ $i ]['failed_validation']  = true;
		$qty_fields[ $i ]['validation_message'] = sprintf( $min_qty_message, $min_qty, $min_qty - $quantity );
	}

	$result['is_valid'] = false;
	$result['form']     = $form;

	return $result;
}
