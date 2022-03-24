/**
 * Gravity Perks // GP Address Autocomplete // Require Place Selection
 * http://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instruction Video: https://www.loom.com/share/e97dee6de5fa4741a1171f6d6e39b95d
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 *     3. Change addressFieldId to the Address Field's ID
 */
// Snippet variables
var addressFieldId = 1;
// End snippet variables

var $ = window.jQuery;
var addAction = window.gform.addAction;

// Set all inputs in the Address Field to read only by default
$("#field_GFFORMID_" + addressFieldId + " input").prop("readonly", true);

// Disable individual options in the select as select's cannot be readonly.
// When an Address is selected, the selected country will have the disabled prop removed.
$("#field_GFFORMID_" + addressFieldId + " select option").prop(
  "disabled",
  true
);

// Re-enable Address Line 1
$("#input_GFFORMID_" + addressFieldId + "_1").prop("readonly", false);

addAction("gpaa_fields_filled", function (place, instance, formId, fieldId) {
  if (formId !== GFFORMID || fieldId !== addressFieldId) {
    return;
  }

  var $addressLine1 = jQuery("#input_GFFORMID_" + fieldId + "_1");
  $addressLine1.data("gpaa-filled-value", $addressLine1.val());

  // Re-disable all countries and re-enable the selected country
  $("#field_GFFORMID_" + fieldId + " select option").prop("disabled", true);
  $("#field_GFFORMID_" + fieldId + " select option:selected").prop(
    "disabled",
    false
  );
});

$("#input_GFFORMID_" + addressFieldId + "_1").on("blur", function () {
  var $this = $(this);
  var filledValue = $this.data("gpaa-filled-value");

  if (!filledValue) {
    filledValue = "";
  }

  $this.val(filledValue);
});
