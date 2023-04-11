<?php
/**
 * Graivty Wiz // Gravity Forms // Move Currency Symbol from the Right to the Left (i.e. "0,00 €" to "€ 0,00")
 * http://gravitywiz.com/how-do-i-move-the-currency-symbol-from-the-right-to-the-left-for-gravity-forms/
 */
add_filter( 'gform_currencies', function( $currencies ) {
	$currencies['EUR']['symbol_left'] = '&#8364;';
	$currencies['EUR']['symbol_right'] = '';
	return $currencies;
} );
