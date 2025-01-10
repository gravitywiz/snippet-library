<?php
/**
 * Gravity Perks // GP Address Autocomplete // Show Address in a Specific Language
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install snippet per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *     2. Update $language with the ISO 639 two-letter lowercase abbreviation of the desired language e.g. 'en' for English (https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes).
 */
add_filter( 'script_loader_src', function( $src, $handle ) {
	if ( 'gp-address-autocomplete-google' !== $handle ) {
		return $src;
	}

	$language = '';

	return add_query_arg( 'language', $language, $src );
}, 10, 2 );
