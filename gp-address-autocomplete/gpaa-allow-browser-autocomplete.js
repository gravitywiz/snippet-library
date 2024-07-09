/**
 * Gravity Perks // GP Address Autocomplete // Allow Browser Autocomplete
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free [Gravity Forms Code Chest](https://gravitywiz.com/gravity-forms-code-chest/) plugin.
 *     2. Copy and paste the snippet into the JavaScript section of Code Chest for the form you wish to apply this snippet to.
 */
window.gform.addFilter('gpaa_prevent_browser_autocomplete', function( preventBrowserAutocomplete, instance, formId, fieldId ) {
	return false;
});
