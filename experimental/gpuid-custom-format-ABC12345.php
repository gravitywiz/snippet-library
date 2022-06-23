<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Gravity Perks // Unique ID // Custom Format: `ABC12345`
 *
 * Generate a Unique ID in the following format `ABC12345` where each letter can be any letter between A and Z and each
 * digit can be any number between 1 and 9.
 *
 * Configuration instructions are inline.
 */
add_filter( 'gpui_unique_id', function( $unique, $form_id, $field_id ) {
	// Update the "123" to your form ID and the "4" to your Unique ID field's ID.
	if ( $form_id == 123 && $field_id == 4 ) {
		$unique = '';
		foreach ( range( 0, 2 ) as $index ) {
			$unique .= chr( rand( 65, 90 ) );
		}
		$unique .= rand( 10000, 99999 );
	}
	return $unique;
}, 10, 3 );
