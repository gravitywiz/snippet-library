<?php
/**
 * Gravity Perks // File Renamer // Global Filename Template
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * This snippet puts all files in an entry-specific subfolder and increments the filename with its count.
 *
 * Requires GP File Renamer v1.0.5+.
 */
add_filter( 'gpfr_filename', function( $renamed_file, $file, $entry, $form, $field ) {
	$renamed_file = gp_file_renamer()->get_template_value( '{entry:id}/{filename}-{i}', $file, $entry, $form, $field );
	return $renamed_file;
}, 10, 5 );
