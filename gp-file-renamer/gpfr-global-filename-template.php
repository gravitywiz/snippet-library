<?php
/**
 * Gravity Perks // GP File Renamer // Global Filename Template
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * This snippet puts all files in an entry-specific subfolder and increments the filename with its count.
 */
add_filter( 'gpfr_filename', function( $renamed_file, $file, $entry ) {
	$renamed_file = gp_file_renamer()->rename_file( $file, '{entry:id}/{filename}-{i}', $entry );
	return $renamed_file;
}, 10, 3 );
