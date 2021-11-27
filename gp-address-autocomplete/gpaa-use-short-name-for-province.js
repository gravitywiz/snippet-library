/**
 * Gravity Perks // Address Autocomplete // Always Use Short Name for State / Province / Region
 * https://gravitywiz.com/documentation/gravity-forms-address-autocompate/
 *
 * This was develope for a customer using Address Autocomplete in Italy. The province was retuned as 
 * "Città Metropolitana di Torino" where they wanted to use the province code instead (e.g. "TO").
 */
gform.addFilter( 'gpaa_values', function( values, place ) {
	for ( var i = 0; i < place.address_components.length; i++ ) {
		var component = place.address_components[i];
		if ( component.long_name === values.stateProvince ) {
			values.stateProvince = component.short_name;
		}
	}
	return values;
} );
