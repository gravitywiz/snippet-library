/**
 * Gravity Perks // GP Address Autocomplete // Use Full Street Address
 *
 * For some countries like Poland, when entering an address with a street number,
 * the place result may sometimes truncate it. This snippet will prepend the street number if it is not
 * included in the result.
 *
 * http://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addFilter('gpaa_values', function(values, place, instance) {
	var fullAddress = instance.inputs.address1.value;
	var extractedAddress = '';
	// Split the address parts over comma.
	var parts = fullAddress.split(',');
	var commaCount = parts.length - 1;

	// For long Multi-Comma Addresses like: Via Luigi Einaudi, 2/4, Barletta, Province of Barletta-Andria-Trani, Italy
	if (commaCount >= 4) {
		// get the address before the last couple commas
		extractedAddress = parts.slice(0, -3).join(',');
	} else {
		// For Most addresses.
		extractedAddress = fullAddress.split(',')[0];
		// For addresses like "Erzsébet park 2, Kecskemét" which return city name first "Kecskemét, Erzsébet Street 2, Hungary"
		if ( extractedAddress == values.city ) {
			extractedAddress = fullAddress.split(',')[1];
		}
	}
	values.address1 = extractedAddress.trim();
	return values;
});
