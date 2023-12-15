<?php
/**
 * Gravity Perks // GP Address Autocomplete // Show ZIP as Results
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install snippet per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *     2. Update FORMID to the Form ID and FIELDID to the Address Field ID.
 *     3. Hide the Address Line 1, Address Line 2, City, and State inputs from the Form Editor if desired.
 *     4. Install accompanying gpaa-autocomplete-zip.js snippet
 */
add_filter( 'gpaa_init_args_FORMID_FIELDID', function( $args ) {
	$args['inputSelectors']['autocomplete'] = '#input_FORMID_FIELDID_5';

	return $args;
});
