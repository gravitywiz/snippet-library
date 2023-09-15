/**
 * Gravity Perks // Address Autocomplete // State/Province Support for Gravity Forms Address Enhanced
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Add support for automatically selecting the state/province when using the Gravity Forms Address Enhanced plugin.
 * The issue is that GF Address Enhanced does not preserve the populated value in the input when the field is converted
 * into a select after the country is either manually changed or autmatically populated by GPAA.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addAction( 'gpaa_fields_filled', function( place, gpaa, formId, fieldId ) {
	var stateProvince = '';
	for ( var i = 0; i < place.address_components.length; i++ ) {
	    var component = place.address_components[i];
		if ( component.types.indexOf( "administrative_area_level_1" ) !== -1 ) {
			stateProvince = component.short_name;
		}
	}
	if ( stateProvince ) {
		$( gpaa.getInputEls( gpaa.inputSelectors ).stateProvince ).val( stateProvince );
	}
} );
