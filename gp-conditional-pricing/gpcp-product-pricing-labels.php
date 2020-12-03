<?php
/**
 * Gravity Perks // GP Conditional Pricing // Display Price Labels (PHP)
 * http://gravitywiz.com/documentation/gravity-forms-conditional-pricing/
 *
 * This snippet requires the JS counterpart gpcp-product-pricing-labels.js
 */
add_filter( 'gform_field_choice_markup_pre_render', function ( $choice_markup, $choice, $field, $value ) {

	// Edit the template here. Use {label} to represent the original choice label and {price} where ever you would like to include the choice price.
	$template = '{label} - {price}';

	// No need to edit below.
	if ( $field->type === 'product' ) {
		$template = str_replace( '{label}', $choice['text'], $template );
		$replace = sprintf( ' data-gpcp-template="%s">%s<', $template, str_replace( '{price}', GFCommon::to_money( $choice['price'] ), $template ) );
		$choice_markup = str_replace( ">{$choice['text']}<", $replace, $choice_markup );;
	}

	return $choice_markup;
}, 10, 4 );
