<?php
/**
 * Gravity Wiz // Address Autocomplete // Restrict Bounds of Autocomplete Results
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * By default, Address Autocomplete can only limit the country for which results are returned.
 * It will also bias results towards the user's current location. Use this snippet to define a
 * circular area to limit results to by specifying your area's center latitude and longitude and
 * the radius in miles of your circular area.
 */
// Update "123" to the ID of your form.
add_action( 'gform_pre_enqueue_scripts_123', function() {
	?>
	<script>
		gform.addFilter( 'gpaa_autocomplete_options', function( options, gpaa, formId ) {

			// Update miles to the average
			var miles     = 100;
			var centerLat = 34.5199;
			var centerLng = -105.8701;

			var metersInMile = 1609.344;
			var autocompleteCircleBounds = new google.maps.Circle( { center: new google.maps.LatLng( centerLat, centerLng ), radius: miles * metersInMile } );

			options.bounds       = autocompleteCircleBounds.getBounds();
			options.strictBounds = true;

			return options;
		} );
	</script>
	<?php
} );
