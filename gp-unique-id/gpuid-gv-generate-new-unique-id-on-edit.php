<?php
/**
 * Gravity Perks // Unique ID // Generate New Unique ID on GravityView Edit
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 */
add_action( 'gravityview/edit_entry/after_update', function( $form, $entry_id ) {
	// Update "123" to your form ID.
	if ( $form['id'] == 123 ) {
		/** @var $uid_field \GF_Field_Unique_ID */
		// Update "4" to your Unique ID field ID.
		$uid_field = GFAPI::get_field( $form['id'], 4 );
		$uid_field->save_value( GFAPI::get_entry( $entry_id ), $uid_field, false );
	}
}, 10, 3 );
