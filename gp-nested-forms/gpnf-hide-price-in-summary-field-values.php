<?php
/**
 * Gravity Perks // Nested Forms // Hide Price in Summary Field Values
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Choice-based Product and Option fields will display the price of the selected option (e.g. "Option Name ($1.00)").
 * This snippet will hide the price and only display the option name (e.g. "Option Name").
 */
add_filter( 'gpnf_display_value', function( $display_value, $field, $form, $entry ) {

	if ( ! $field->enablePrice || empty( $field->choices ) ) {
		return $display_value;
	}

	$values = $display_value['value'];
	if ( ! is_array( $values ) ) {
		$values = array( $values );
	}

	foreach ( $values as $_value ) {

		$parts = explode( '|', $_value );
		if ( empty( $parts[1] ) ) {
			continue;
		}

		$price                  = GFCommon::to_money( $parts[1], $entry['currency'] );
		$display_value['label'] = str_replace( "($price)", '', $display_value['label'] );

	}

	return $display_value;
}, 10, 4 );
