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
	values.address1 = fullAddress.split(',')[0].trim();
	return values;
});
