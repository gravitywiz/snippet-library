<?php
/**
 * GP Unique ID // Gravity Perks // Populate Unique ID via Dynamic Population
 * https://gravitywiz.com/documetnation/gravity-forms-unique-id/
 *
 * Getting started:
 *
 * 1 - Copy and paste this code into your theme's functions.php file.
 * 2 - Add a Single Line Text field (or any field you would like to populate with a unique ID) to your form.
 * 3 - Enable the "Allow field to be populated dynamically" setting under the Advanced tab on the field settings for the newly created field.
 * 4 - Set "uid" as the value for the Paramater Name setting (screenshot: https://gwiz.io/2HhtBTa).
 */
add_filter( 'gform_field_value_uid', function( $value, $field ) {

	// Update what type of unique ID you would like to generate. Accepts 'alphanumeric', 'numeric', or 'sequential'.
	$type_of_id = 'alphanumeric';

	// How long would you like your unique ID to be?
	$length = 4;

	if ( function_exists( 'gp_unique_id' ) ) {
		// This filter will be called multiple times per page load. Only generate a new ID once.
		static $gw_uid                                  = false;
		$field[ gp_unique_id()->perk->key( 'type' ) ]   = $type_of_id;
		$field[ gp_unique_id()->perk->key( 'length' ) ] = $length;
		if ( ! $gw_uid ) {
			$gw_uid = gp_unique_id()->get_unique( $field->formId, $field );
		}
		$value = $gw_uid;
	}

	return $value;
}, 10, 2 );
