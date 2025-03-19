/**
 * Gravity Perks // GP Address Autocomplete // Populate Inputs with Latitude and Longitude
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom JavaScript for Gravity Forms plugin.
 *         Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 *     3. Update the selectors below to match the field IDs that will be populated with lat/lng.
 */
window.gform.addAction('gpaa_fields_filled', (place) => {
	$('#input_GFFORMID_3').val(place.geometry.location.lat());
	$('#input_GFFORMID_4').val(place.geometry.location.lng());
});
