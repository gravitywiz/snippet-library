/**
 * Gravity Perks // GP Address Autocomplete // Populate Province when GF Address Enhanced is enabled
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom JavaScript for Gravity Forms plugin.
 *         Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */

gform.addAction('gpaa_fields_filled', function (place, instance, formId, fieldId) {
	place.address_components.forEach( function( component ) { 
		if ( component.types.indexOf( 'administrative_area_level_1' ) !== -1 ) {
			// Update "1" to the field ID in which you would GPAA and GF Address Enhanced enabled
			$( '#input_GFFORMID_1_4 option[value="' + component.short_name + '"]' ).attr('selected', 'selected');
		}
	} );
} );
