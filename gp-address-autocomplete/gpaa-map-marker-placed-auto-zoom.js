
/**
 * Gravity Perks // GP Address Autocomplete // Auto Zoom When Marker is Placed
 *
 * Easily set a new map zoom level when a marker is placed.
 *
 * http://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */

/**
 * Adjust this value to customize the zoom level when a marker is placed.
 * Smaller numbers are more zoomed out and larger numbers are more zoomed in.
 */
var zoomLevel = 20;

window.gform.addAction('gpaa_marker_set', function(args) {
	args.map.setZoom(zoomLevel);
})
