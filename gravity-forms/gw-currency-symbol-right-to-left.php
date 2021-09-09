<?php
/**
 * Graivty Wiz // Gravity Forms // Move Currency Symbol from the Right to the Left (i.e. "0,00 €" to "€ 0,00")
 * http://gravitywiz.com/how-do-i-move-the-currency-symbol-from-the-right-to-the-left-for-gravity-forms/
 */
add_filter( 'gform_currencies', function( $currencies ) {
	$currencies['EUR'] = array(
		'name'               => esc_html__( 'Euro', 'gravityforms' ),
		'symbol_left'        => '&#8364;',
		'symbol_right'       => '',
		'symbol_padding'     => ' ',
		'thousand_separator' => '.',
		'decimal_separator'  => ',',
		'decimals'           => 2
	);
	return $currencies;
} );
