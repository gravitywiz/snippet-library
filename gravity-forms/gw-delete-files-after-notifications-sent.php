<?php
/**
 * Gravity Wiz // Gravity Forms // Delete Files After Notifications Sent
 * https://gravitywiz.com/
 *  
 * This snippet will automatically delete all uploaded files after notifications have been sent. This pairs well with
 * Gravity Forms' "Attach uploaded fields to notification" notification option, allowing you to attach the files to a
 * notification without preserving the files on the file server.
 *
 * WARNING! Files will be deleted after submission regardless of whether notifications have been configured. Only
 * use this snippet if you have configuration your notifications to attach the files.
 *
 * Optionally, allow the submitter to decide if the files should be deleted or preserved by specifying a field ID and
 * corresponding field value that confirms files should be deleted. For example, a Drop Down may have two
 * choices: "Preserve Files" and "Delete Files". Specify "Delete Files" as the $delete_value below and files will only
 * be deleted if that choice is selected.
 */
add_filter( 'gform_after_submission_123', function( $entry, $form ) {

	// Update "4" to the field ID whose value is checked to determine if files should be deleted.
	$target_field_id = 4;

	// Update "Delete Files" to the value of the target field that will confirm files should be deleted.
	$delete_value = 'Delete Files';

	if ( ! isset( $target_field_id ) || rgar( $entry, $target_field_id ) == $delete_value ) {
		GFFormsModel::delete_files( $entry['id'], $form );

		$field_types = GFFormsModel::get_delete_file_field_types( $form );
		$fields      = GFFormsModel::get_fields_by_type( $form, $field_types );
		foreach ( $fields as $field ) {
			GFAPI::update_entry_field( $entry['id'], $field->id, '' );
		}
	}

}, 10, 2 );
