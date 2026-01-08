/**
 * Gravity Perks // Address Autocomplete // Capture Neighborhood Separately
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * To capture the county instead, replace 'neighborhood' with 'administrative_area_level_2'.
 */
gform.addAction('gpaa_fields_filled', function (place, instance, formId, fieldId) {
	place.address_components.forEach( function( component ) { 
		if ( component.types.indexOf( 'neighborhood' ) !== -1 ) {
    	// Update "4" to the field ID in which you would like to capture the Neighborhood.
			$( '#input_GFFORMID_4' ).val( component.long_name );
		}
	} );
	return place;
} );
