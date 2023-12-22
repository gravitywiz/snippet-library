<?php
/**
 * Gravity Perks // Nested Forms // Hide Price in Summary Field Values
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Choice-based Product and Option fields will display the price of the selected option (e.g. "Option Name ($1.00)").
 * This snippet will hide the price and only display the option name (e.g. "Option Name").
 */
add_filter( 'gpnf_display_value', function( $value, $field, $form, $entry ) {
	if ( GFCommon::is_product_field( $field->type ) && ! empty( $field->choices ) ) {
		$bits = explode( '|', $value['value'] );
		$choice = $field->get_selected_choice( array_shift( $bits ) );
		$value['label'] = $choice['text'];
	}
	return $value;
}, 10, 4 );
