<?php
/**
 * Gravity Perks // File Renamer // Global Filename Template
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * This snippet puts all files in an entry-specific subfolder and increments the filename with its count.
 *
 * Requires GP File Renamer v1.0.5+.
 */
add_filter( 'gpfr_filename_template', function ( $template, $filename, $form, $field, $entry ) {
	return '{entry:id}/{filename}-{i}';
}, 10, 5 );
