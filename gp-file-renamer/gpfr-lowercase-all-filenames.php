<?php
/**
 * Gravity Perks // GP File Renamer // Lowercase All Filenames
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * This snippet will automatically lowercase all filenames.
 */
add_filter( 'gpfr_filename', function( $renamed_file, $file, $entry ) {
	// Important note, $renamed_file is the entire path.
	$lowercase_filename = strtolower( wp_basename( $renamed_file ) );

	return str_replace( wp_basename( $renamed_file ), $lowercase_filename, $renamed_file );
}, 10, 3 );
