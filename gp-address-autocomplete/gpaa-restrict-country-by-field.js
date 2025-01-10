/**
 * Gravity Perks // Address Autocomplete // Restrict Country by Field
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Restrict autocomplete results to a specific country (or countries) for a specific field.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Follow the inline instructions to configure this for your specific field.
 */
gform.addFilter( 'gpaa_autocomplete_options', function( autocompleteOptions, gpaa, formId, fieldId ) {
	// Update "1" to your Address field ID.
	if ( formId != GFFORMID || fieldId != 1 ) {
		return autocompleteOptions;
	}
	if ( typeof autocompleteOptions.componentRestrictions !== 'object' ) {
		autocompleteOptions.componentRestrictions = {};
	}
	// Update "DE" to the country to which you would like to restrict results.
	autocompleteOptions.componentRestrictions.country = [ 'DE' ];
	return autocompleteOptions;
} );
