/**
 * Gravity Perks // Address Autocomplete // Trim US Zip Codes to First 5 Digits
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
 gform.addFilter( 'gpaa_values', function( values, place ) {
	values.postcode = values.postcode.split( '-' )[0];
	return values;
} );
