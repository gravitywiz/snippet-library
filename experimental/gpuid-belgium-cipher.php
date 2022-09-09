<?php
/**
 * Gravity Perks // Unique ID // Generate Belgium Cipher
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Instructions:
 *
 *  1. Copy and paste this code into your theme's functions.php file or wherever you include custom PHP snippets.
 *  2. Add a Unique ID field to your form.
 *  3. Configure it to type "Numeric" with a length of "10".
 *  4. Add a Hidden field to your form.
 *  5. Enable the "Allow field to be dynamically populated" option on the Advanced tab.
 *  6. Set the "Parameter Name" to "cipher_code_FIELDID", replacing FIELDID with the ID of the Unique ID field.
 */
add_filter( 'gform_entry_post_save', function( $entry, $form ) {

	$key = 'ciphered_code';

	foreach( $form['fields'] as $field ) {

		if ( strpos( $field->inputName, $key ) === false ) {
			continue;
		}

		$source_field_id = (int) str_replace( $key . '_', '', $field->inputName );
		if ( ! $source_field_id ) {
			continue;
		}

		$value           = (int) rgar( $entry, $source_field_id );
		$mod97           = $value % 97;
		$last_two        = str_pad( $mod97 ? $mod97 : '97', 2, '0', STR_PAD_LEFT );
		$combined        = $value . $last_two;
		$code            = '+++' . substr( $combined, 0, 3 ) . '/' . substr( $combined, 3, 4 ) . '/' . substr( $combined, 7, 5 ) . '+++';

		$entry[ $field->id ] = $code;
		GFAPI::update_entry_field( $entry['id'], $field->id, $code );
	}

	return $entry;
}, 9, 2 );
