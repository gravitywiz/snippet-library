<?php
/**
 * Gravity Perks // Unique ID // Generate Unique ID from Input Mask
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 */
add_filter( 'gpui_unique_id', function( $unique, $form_id, $field_id ) {
	// Update "123" to your form ID and "4" to your Unique ID's field ID.
	if ( $form_id == 123 && $field_id == 4 ) {
		/*
		 * Define your input mask.
		 * - "a" will be any lowercase letter a through z.
		 * - "A" will be any uppercase letter A through Z.
		 * - "9" will be any number 0 - 9.
		 */
		$mask   = 'aa 999 999';
		$unique = gpui_generate_unique_id_from_mask( $mask );
	}
	return $unique;
}, 10, 3 );

if ( ! function_exists( 'gpui_generate_unique_id_from_mask' ) ) {
	function gpui_generate_unique_id_from_mask( $mask ) {
		$uid   = '';
		$chars = str_split( $mask );
		foreach ( $chars as $char ) {
			switch ( $char ) {
				case 'a':
					$char = chr( rand( 97, 122 ) );
					break;
				case 'A':
					$char = chr( rand( 65, 90 ) );
					break;
				case '9':
					$char = rand( 0, 9 );
					break;
			}
			$uid .= $char;
		}
		return $uid;
	}
}
