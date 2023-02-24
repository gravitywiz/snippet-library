/**
 * Gravity Perks // Advanced Save and Continue // Toggle GPASC with a Dropdown
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 *
 * This snippet allows you to toggle GPASC client side with a dropdown.
 */

// change this to match your form ID
var formId = 1;
// change this to match the dropdown field you'd like to use to control this
var dropdownFieldId = 1;

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
	return getDropdownValue() === '1';
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
	if (event.target.value === '1') {
		enableGPASC();
	} else {
		disableGPASC();
	}
});
