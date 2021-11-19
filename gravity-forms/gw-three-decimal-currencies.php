<?php
/**
 * Gravity Wiz // Gravity Forms // Three Decimal Currencies
 * https://gravitywiz.com/how-to-add-three-decimals-with-gravity-form-currencies/
 */
add_filter( 'gform_currencies', function( $currencies ) {
	$currencies['EUR']['decimals'] = 3;
	return $currencies;
} );
