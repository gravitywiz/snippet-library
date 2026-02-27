<?php
/**
 * Gravity Perks // Unique ID // Copy Unique ID to Another Field
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Instruction Video: https://www.loom.com/share/16fa4ef18bb34299b8ef91fca2085060
 *
 * Copy a generated Unique ID value to one or more other fields after entry save.
 *
 * Plugin Name:  GP Unique ID - Copy Unique ID to Another Field
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-unique-id/
 * Description:  Copy a generated Unique ID value to one or more other fields after entry save.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
// update the "123" to your form ID
add_filter( 'gform_entry_post_save_123', function( $entry ) {

	// update "1" to your Unique ID field's field ID
	$uid_field_id = 1;

	// update "2" and "3" to whatever field IDs you would like to copy the unique ID, if you only need one, format it like this: array( 2 )
	$copy_to_field_ids = array( 2, 3 );

	foreach ( $copy_to_field_ids as $copy_to_field_id ) {
		$entry[ $copy_to_field_id ] = $entry[ $uid_field_id ];
	}

	GFAPI::update_entry( $entry );

	return $entry;
} );
