/**
 * Gravity Perks // GP Address Autocomplete // Re-add Street Number to Address
 *
 * In some situations with Google Places Autocomplete, the street number may be dropped after autocompletion if Google
 * does not think the address exists. Oftentimes, the address will show in the results, but the actual street number
 * will be un-bold meaning it's not matching.
 *
 * After selecting the place, Places Autocomplete will briefly populate the input with the street number, but the actual
 * `google.maps.places.PlaceResult` result will not contain it.
 *
 * This snippet will use the address that is briefly populated if it detects that the populated place does not contain
 * a street number, but a street number was originally entered.
 *
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instructions:
 *     1. Install our free Custom JavaScript for Gravity Forms plugin.
 *         Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom JavaScript for Gravity Forms plugin.
 */
window.gform.addFilter('gpaa_values', function( values, place, gpaa, formId, fieldId ) {
    /* If the value coming back for Address 1 doesn't contain a street number, let's pull it from the input as Places Autocomplete will briefly populate the input. */
    if ( ! values.address1.match( /^(\d+)\s+.*/ ) && gpaa.inputs.address1.value.match( /^(\d+)\s+.*,/ ) ) {
        var adrSplit = gpaa.inputs.address1.value.split(',');

        values.address1 = adrSplit[0];
    }

    return values;
});
