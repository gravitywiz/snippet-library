<?php
/**
 * Gravity Wiz // Gravity Forms // Display Price for Drop Down and Radio Button Product Fields
 * https://gravitywiz.com/
 *
 * Appends the price of a product to the Labels of a Dropdown and Radio Button Product Fields.
 *
 * Source: https://www.gravityhelp.com/documentation/article/gform_field_choice_markup_pre_render/#2-include-price-for-product-fields
 *
 * Plugin Name:  Gravity Forms — Display Price for Drop Down & Radio Button Products
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Appends the price of a product to the Labels of a Dropdown and Radio Button Product Fields.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_field_choice_markup_pre_render', function ( $choice_markup, $choice, $field, $value ) {

	if ( $field->type == 'product' ) {
		$search  = sprintf( '>%s<', esc_html( $choice['text'] ) );
		$replace = sprintf( '>%s - %s<', $choice['text'], GFCommon::to_money( $choice['price'] ) );
		return str_replace( $search, $replace, $choice_markup );
	}

	return $choice_markup;
}, 10, 4 );
