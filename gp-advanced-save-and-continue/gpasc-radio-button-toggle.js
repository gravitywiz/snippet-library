/**
 * Gravity Perks // Advanced Save and Continue // Toggle GPASC with Radio Buttons
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 *
 * This snippet allows you to toggle GPASC client side with radio buttons.
 * 
 * Instructions:
 *  1. Add snippet to form using https://gravitywiz.com/gravity-forms-custom-javascript/
 *  2. Customize formId for the form you want this to apply to.
 *  3. Customize radioButtonFieldId to the field you want this to apply to.
 *  4. Customize enableInputId and disableInputId per the comments above each variable.
 */

// change this to match your form ID
var formId = 1;
// change this to match the radio button field you'd like to use to control this
var radioButtonFieldId = 2;
// change this to match the radio button input ID you'd like to use to enable GPASC
var enableInputId = 0;
// change this to match the radio button input ID you'd like to use to disable GPASC
var disableInputId = 1;

var gpascInstanceKey = 'GPASC_' + formId;
var enabledSelector = '#choice_' + formId + '_' + radioButtonFieldId + '_' + enableInputId;
var disabledSelector = '#choice_' + formId + '_' + radioButtonFieldId + '_' + disableInputId;

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

var enabledRadioButton = $(enabledSelector)[0];
var disabledRadioButton = $(disabledSelector)[0];

if (enabledRadioButton && enabledRadioButton.checked ) {
	enableGPASC()
} else if (disabledRadioButton && disabledRadioButton.checked) {
	disableGPASC();
}

$(enabledSelector).on('change', function(event) {
  if (event.target.checked) {
	enableGPASC();
  }
})

$(disabledSelector).on('change', function(event) {
  if (event.target.checked) {
	disableGPASC();
  }
})
