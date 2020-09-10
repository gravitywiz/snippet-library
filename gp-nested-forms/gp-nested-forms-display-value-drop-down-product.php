<?php
/**
 * Gravity Perks // GP Nested Forms // Hide Price in Nested Form Field Display Value for Drop Down Product Fields
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * "My Product Choice ($10.00)" => "My Product Choice"
 */
add_filter( 'gpnf_select_display_value', function( $value, $field ) {
	if( $field->type == 'product' ) {
		$index = strpos( $value['label'], '(' );
		if( $index !== false ) {
			$value['label'] = substr( $value['label'], 0, $index );
		}
	}
	return $value;
}, 10, 2 );