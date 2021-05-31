<?php
/**
 * Gravity Perks // Unique ID // Custom Format: `ABC12345`
 * 
 * Generate a Unique ID in the following format `ABC12345` where each letter can be any letter between A and Z and each 
 * digit can be any number between 1 and 9.
 */
add_filter( 'gpui_unique_id', function( $unique, $form_id, $field_id ) {
	if ( $form_id == 300 && $field_id == 1 ) {
		$unique = '';
		foreach( range( 0, 2 ) as $index ) {
			$unique .= chr( rand( 65, 90 ) );
		}
		$unique .= rand( 10000, 99999 );
	}
	return $unique;
}, 10, 3 );
