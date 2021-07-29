<?php
/**
 * Gravity Wiz // Gravity Forms // Capture File Extension
 * https://gravitywiz.com/
 *
 * Capture the file extension of any file uploaded into a File Upload field on submission. 
 * This only works with single File Upload fields.
 */
// Update "123" to your form ID.
add_action( 'gform_entry_post_save_123', function( $entry, $form ) {

	// Update "4" to the ID of your File Upload field.
	$file_upload_field_id = 4;
  
	// Update "5" to the ID of the field in which the file extension should be captured.
	$ext_field_id = 5;

	$ext                    = pathinfo( rgar( $entry, $file_upload_field_id ), PATHINFO_EXTENSION );
	$entry[ $ext_field_id ] = $ext;

	GFAPI::update_entry_field( $entry['id'], $ext_field_id, $ext );

	return $entry;
}, 10, 2 );
