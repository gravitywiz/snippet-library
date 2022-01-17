/**
 * Gravity Perks // GP Address Autocomplete // Use Single Line Text field as autocomplete input
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instruction Video: https://www.loom.com/share/680513c2f0dc403aa6a001f9950f4e77
 *
 * This snippet allows you to use other form Fields as input selectors for GP Address Autocomplete
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
 gform.addFilter('gpaa_values', function(values, place) {
	values.autocomplete = place.formatted_address;

	return values;
});
