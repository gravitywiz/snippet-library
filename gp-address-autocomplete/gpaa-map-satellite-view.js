/**
 * Gravity Perks // GP Address Autocomplete // Map Satellite View
 *
 * Use the "satellite" map type view for Map Fields.
 * Documation for this and other map types can be found here: https://developers.google.com/maps/documentation/javascript/maptypes
 *
 * http://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addFilter(
	'gpaa_map_options',
  	function( options, formId, fieldId ) {
    	// you can optionally add checks here using formId and fieldId to only apply the filter to specific forms/fields.
    	options.mapTypeId = google.maps.MapTypeId.SATELLITE;
    	return options;
  	}
);
