<?php
/**
 * Gravity Perks // File Renamer // Remove Spaces & Dashes in Filenames
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 */
add_filter( 'gpfr_filename', function( $renamed_file, $file, $entry, $form, $field ) {

	$directory_path    = dirname( $renamed_file );
	$filename          = basename( $renamed_file );
	$modified_filename = str_replace( '-', '', $filename );

	return $directory_path . '/' . $modified_filename;
}, 10, 5 );
