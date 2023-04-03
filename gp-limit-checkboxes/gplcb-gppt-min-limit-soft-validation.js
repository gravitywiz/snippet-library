/**
 * Gravity Perks // GP Limit Checkboxes // Add support for min limit Soft Validation with GP Page Transitions
 *
 * https://gravitywiz.com/documentation/gravity-forms-limit-checkboxes/
 *
 * Requires 1.3.12 or newer of GP Limit Checkboxes.
 *
 * Instructions:
 *   - Install using https://gravitywiz.com/gravity-forms-custom-javascript/
 */
window.gform.addFilter('gppt_validation_result', function (result, gppt, formId) {
	var message = 'You must select at least {0} options.';

	if (formId != GFFORMID) {
		return result;
	}

	var gplc = window.GPLimitCheckboxes && window.GPLimitCheckboxes.instances && window.GPLimitCheckboxes.instances[formId];

	if (!gplc) {
		return result;
	}

	var validationMessage = '<div class="gfield_description validation_message">' + message + '</div>';

	// Loops through groups and validate the ones where the fields are on the current page.
	for (var i = 0; i < gplc.groups.length; i++) {
		var group = gplc.groups[i];
		var fields = group.fields;
		var fieldsVisible = true;
		var groupValidationMessage = validationMessage.format(group.min);

		$(gplc.getSelector(group.fields)).each(function () {
			var fieldVisible = $(this).is(':visible');
			var pageVisible = $(this).closest('.gform_page').is('.swiper-slide-active');

			if (!fieldVisible || !pageVisible) {
				fieldsVisible = false;
				return false;
			}
		});

		if (!fieldsVisible) {
			continue;
		}

		var belowMin = gplc.isGroupBelowMin(group);
		var $parent = $(gplc.getSelector(group.fields)).parents('.gfield');

		$parent.each(function () {
			var $this = $(this);

			if (belowMin) {
				if (!$(this).hasClass(gppt.validationClass.split(' ')[0])) {
					$this.addClass(gppt.validationClass);
					$this
						.children('.ginput_container')
						.after(
							groupValidationMessage
						);
				}
			} else {
				$this.removeClass(gppt.validationClass);
				$this
					.children('.ginput_container')
					.next()
					.remove();
			}
		});

		if (belowMin) {
			return false;
		}
	}

	return result;
});
