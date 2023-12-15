<?php
/**
 * Gravity Wiz // Gravity Forms // Modify Thousandths Separator
 * https://gravitywiz.com/
 *
 * Usage:
 *
 * 1. Modify `$separator` to your desired thousandths separator. This example _removes_ the thousandths separator.
 * 2. Thats it.
 */

add_filter( 'gform_currencies', function( $currencies ) {
	$separator                               = '';
	$currencies['EUR']['thousand_separator'] = $separator;
	return $currencies;
} );
