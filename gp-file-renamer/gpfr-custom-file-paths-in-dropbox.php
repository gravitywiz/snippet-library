<?php
/**
 * Gravity Perks // File Renamer // Set Custom File Path in Dropbox
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * Instruction Video: https://www.loom.com/share/27c13dda5ad349f7a9efb20bd0b1d654
 *
 * Activate this snippet to honor custom file paths (defined in GPFR's Filename Template setting) are honored when a
 * file is uploaded to Dropbox (via the GF Drobox add-on).
 */
add_filter( 'gform_dropbox_folder_path', function( $folder_path, $form, $field_id, $entry, $feed ) {
	$field = GFAPI::get_field( $form, $field_id );
	if ( $field->gpfrTemplate ) {
		$file_url     = $entry[ $field_id ];
		if ( $file_url ) {
      // Note: Using this method means this will only consistently work on initial submission. If Dropbox feed is processed at
      // some later date (either when an entry is edited or as part of a workflow), this may not work as default upload paths
      // are date-sensitive.
			$upload_roots = GF_Field_FileUpload::get_default_upload_roots( $form['id'] );
			$path         = str_replace( $upload_roots['url'], '', $file_url );
			$folder_path .= "/{$path}";
		}
	}
	return $folder_path;
}, 10, 5 );
