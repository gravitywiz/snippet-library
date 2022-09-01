<?php
/**
 * Gravity Perks // GP File Renamer // Lowercase All Filenames
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * This snippet will automatically lowercase all filenames.
 */
add_filter( 'gpfr_filename', function( $renamed_file, $file, $entry ) {
	return strtolower( $renamed_file );
}, 10, 3 );
