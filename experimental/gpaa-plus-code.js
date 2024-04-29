/**
 * Gravity Perks // GP Address Autocomplete // Display Google Plus Code
 *
 * Adds a Google Plus Code to the configured text field when an address is autocompleted.
 *
 * Instructions:
 *     1. Install our free Custom JavaScript for Gravity Forms plugin.
 *         Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 *     3. Update the field id variables to match your the address and text fields you would like this to
 *        apply to.
 */

// update this with the id of the address field you want this to apply to.
var addressFieldId = 1;
// update this with the id of the text field you would like the plus code displayed in.
var plusCodeFieldId = 3;

window.gform.addFilter('gpaa_autocomplete_options', (autocompleteOptions, gpaa, formId, fieldId) => {
  if (formId !== 'GFFORMID' && fieldId !== addressFieldId) {
    return autocompleteOptions;
  }

  autocompleteOptions.fields.push('plus_code');

  return autocompleteOptions;
});

window.gform.addAction('gpaa_fields_filled', (place, instance) => {
  // "plus_code" includes both "global_code" or "compound_code" as documented here: https://developers.google.com/maps/documentation/javascript/reference/places-service#PlacePlusCode
  var plusCode = place?.plus_code?.global_code || 'No Plus Code found';

  $('#input_GFFORMID_' + plusCodeFieldId).val(plusCode);
});
