/**
 * Gravity Perks // GP Address Autocomplete // Allow Browser Autocomplete
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom JavaScript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addFilter('gpaa_prevent_browser_autocomplete', function( preventBrowserAutocomplete, instance, formId, fieldId ) {
	return false;
});
