/**
 * Gravity Perks // GP Address Autocomplete // Always Use Subpremise/Unit Numbers
 *
 * For some countries including Australia and Scotland, when entering an address with a subpremise,
 * the place result may sometimes truncate it. This snippet will prepend the subpremise if it is not
 * included in the result.
 *
 * http://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addFilter('gpaa_values', function(values, place, instance) {
    if (instance.inputs.address1.value.indexOf('/') === -1) {
        return values;
    }

    for (var i = 0; i < place.address_components.length; i++) {
        var component = place.address_components[i];
        var componentType = component.types[0];

        if (componentType !== 'route') {
            continue;
        }

        var subPremisePattern = RegExp('^(.*)' + component.long_name.split(' ', 1)[0]);
        var results = subPremisePattern.exec(instance.inputs.address1.value);
        var streetNumber = results[1].trim().split('/')[1];
		
		// Sometimes Rd/Road is inconsistent, let's use Road.
		values.address1 = values.address1.replace('Rd', 'Road');
		streetNumber = streetNumber.replace('Rd', 'Road');
		
		// Remove trailing comma from streetNumber
		streetNumber = streetNumber.replace(/,$/, '');
				
        if (results && values.address1.indexOf(results[1]) === -1) {
            values.address1 = (results[1].trim() + ' ' + values.address1.replace(streetNumber, '').trim()).trim().replace(/,$/, '');
        }
    }

    return values;
});
