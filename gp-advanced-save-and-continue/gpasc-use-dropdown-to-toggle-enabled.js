/**
 * Gravity Perks // Advanced Save and Continue // Toggle GPASC with a Dropdown
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 *
 * This snippet allows you to toggle GPASC client side with a dropdown.
 * 
 * Instructions:
 *  1. Add snippet to form using https://gravitywiz.com/gravity-forms-custom-javascript/
 *  2. Customize formId to match the form you want to use this with.
 *  3. Customize the dropdownFieldId to match the DropdownField you want to use this with.
 *  4. Customize dropdownEnableValue to the HTML "value" attribute which you want GPASC to
 *     be enabled with. For example, if you want GPASC to be enabled when a dropdown
 *     </option> with HTML value of "enabled" is selected, set this to "enabled".
 */

// change this to match your form ID
var formId = 1;
// change this to match the dropdown field you'd like to use to control this
var dropdownFieldId = 1;
// change this to match the desired "value" (e.g. HTML value attribute) of the Dropdown Field
// that, when selected, should enable GPASC.
var dropdownEnableValue = '1';

var gpascInstanceKey = 'GPASC_' + formId;
var dropdownSelector = '#input_' + formId + '_' + dropdownFieldId;

function enableGPASC() {
  if (window[gpascInstanceKey] && window[gpascInstanceKey].enable) {
    window[gpascInstanceKey].enable();
  }
}

function disableGPASC() {
  if (window[gpascInstanceKey] && window[gpascInstanceKey].enable) {
    window[gpascInstanceKey].disable({ resetCookieModal: true });
  }
}

function getDropdownValue() {
  return $(dropdownSelector).val();
}

function shouldEnable() {
	return getDropdownValue() === dropdownEnableValue;
}

window.gform.addAction('gpasc_js_init', function (formId) {
	if (formId != formId) {
		return;
	}

	if (shouldEnable()) {
		enableGPASC();
	} else {
		disableGPASC();
	}
});

$(dropdownSelector).on('change', function(event) {
	console.log({ event })
	if (event.target.value === dropdownEnableValue) {
		enableGPASC();
	} else {
		disableGPASC();
	}
});
