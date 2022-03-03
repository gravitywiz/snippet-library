<?php
/**
 * Gravity Wiz // Gravity Forms // Append/Prepend Values to Merge Tags
 * https://gravitywiz.com
 *
 * Instruction Video: https://www.loom.com/share/9cc6062f43244aaa8a9cab351f1370b3
 *
 * Use :prepend[value] to prepend a value to the result of a merge tag. Use :append[value] to append a value to the
 * result of a merge tag.
 *
 * Limitations
 *
 * 1. The comma (,) is a reserved character of Gravity Forms' merge tags. Use &comma; instead.
 * 2. Gravity Forms lower-cases all modifier values by default. A value of "Hello" will be rendered as "hello".
 *
 * Plugin Name:  Gravity Forms Append/Prepend Merge Tag Modifiers
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Append or prepend a string to a merge tag's output only when it has a value.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_merge_tag_filter', function( $value, $input_id, $modifier, $field, $raw_values ) {

	if ( empty( $modifier ) || empty( $value ) ) {
		return $value;
	}

	$parse_modifiers = function( $modifiers_str ) {

		preg_match_all( '/([a-z]+)(?:(?:\[(.+?)\])|,?)/i', $modifiers_str, $modifiers, PREG_SET_ORDER );
		$parsed = array();

		foreach ( $modifiers as $modifier ) {

			list( $match, $modifier, $value ) = array_pad( $modifier, 3, null );
			if ( $value === null ) {
				$value = $modifier;
			}

			// Split '1,2,3' into array( 1, 2, 3 ).
			if ( strpos( $value, ',' ) !== false ) {
				$value = array_map( 'trim', explode( ',', $value ) );
			}

			$parsed[ strtolower( $modifier ) ] = $value;

		}

		return $parsed;
	};

	$modifiers = $parse_modifiers( $modifier );

	foreach ( $modifiers as $modifier => $modifier_value ) {
		switch ( $modifier ) {
			case 'append':
				$value .= $modifier_value;
				break;
			case 'prepend':
				$value = $modifier_value . $value;
				break;
		}
	}

	return $value;
}, 10, 5 );
