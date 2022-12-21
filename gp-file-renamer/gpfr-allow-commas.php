<?php

/**
 * Gravity Perks // GP File Renamer // Allow Commas in Filenames
 *
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * This snippet allows commas (,) in files renamed with GPFR.
 */

add_filter(
	'gpfr_sanitize_file_name_chars',
	function ( $special_chars, $filename_raw ) {
		return array_filter($special_chars, function ( $char ) {
			return $char !== ',';
		} );
	},
	10,
	2
);
