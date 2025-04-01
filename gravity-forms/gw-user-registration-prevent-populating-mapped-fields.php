<?php
/**
 * Gravity Wiz // Gravity Forms User Registration // Prevent Population Of Mapped Fields With The Saved Values
 * https://gravitywiz.com/
 *
 * Prevent population of mapped fields with the saved values. This snippet prevent the population of mapped fields with the saved values while displaying the form.
 *
 * Instructions:
 *
 *  1. Install the snippet.
 *     https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_action( 'gform_user_registration_user_data_pre_populate', function ( $mapped_fields, $form, $feed ) {
	// Replace "1" with the form ID you want to prevent the population of mapped fields.
	if ( $form['id'] !== 1 ) {
		return $mapped_fields;
	}

	// Replace "3" with the field ID you want to display empty.
	$mapped_fields[3] = '';

	// Want to overwrite all mapped fields? Uncomment the line below.
	// $mapped_fields = array();

	return $mapped_fields;
}, 10, 3 );
