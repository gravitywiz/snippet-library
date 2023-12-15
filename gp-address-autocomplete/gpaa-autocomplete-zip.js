/**
 * Gravity Perks // GP Address Autocomplete // Show ZIP as Results
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom JavaScript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 *     3. Install accompanying gpaa-autocomplete-zip.php snippet
 *     4. Change addressFieldId to the Address Field's ID
 */
window.gform.addFilter( 'gpaa_autocomplete_options', function( options ) {
	options.types = ['postal_code'];

	return options;
} );
