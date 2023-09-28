/**
 * Gravity Perks // GP Address Autocomplete // Set Marker From Dynamic Address Field Input
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 * 
 * Minimum Requirements:
 *
 * GP Address Autocomplete: 1.2.16
 *
 * Instructions:
 *     1. Install our free Custom JavaScript for Gravity Forms plugin.
 *	      Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Install the free Early Init Scripts snippet
 *        Get the code here: https://github.com/gravitywiz/snippet-library/blob/master/experimental/gfjs-early-init-scripts.php
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 * 	   3. Set up "dynamic" field population for the address field.
 *     3. update `addressFieldId` to match the address field ID.
 */
 var addressFieldId = 1;

 window.gform.addAction(
	 'gpaa_map_initialized',
	 function (map, fieldId) {
		 var address = '';
		 var i = 1;
		 while (i < 7) {
			 var nextPart = jQuery(
				 `#input_GFFORMID_${addressFieldId}_${i}`
			 ).val();
 
			 if (nextPart) {
				 address += nextPart + ' ';
			 }
 
			 i++;
		 }
 
		 if (!address) {
			 return;
		 }
 
		 var service = new google.maps.places.PlacesService(map);
		 service.findPlaceFromQuery(
			 {
				 query: address,
				 // This queries for a minimal amount of data in order to save on unecssary API charges.
				 // You can query for all fields by passing 'ALL' as documented here: https://developers.google.com/maps/documentation/javascript/reference/places-service#FindPlaceFromQueryRequest
				 fields: ['geometry.location'],
			 },
			 function (results, status) {
				 if (status !== 'OK' || !results || results.length === 0) {
					 return;
				 }
 
				 var location = results[0].geometry.location;
				 var mapController = window['gp_address_autocomplete_map_field_' + fieldId];
 
				 mapController.setMarker({
					 lat: location.lat(),
					 lng: location.lng(),
				 });
			 }
		 );
	 }
 );	
