/**
 * Gravity Perks // Auto List Field // Count Only Non Blank Rows
 * https://gravitywiz.com/documentation/gravity-forms-auto-list-field/
 *
 * Count only non-blank rows in a List field.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter('gform_merge_tag_value_pre_calculation', function (value, match, isVisible, formulaField, formId) {
	if (typeof match[3] === 'undefined' || match[3].indexOf(':count') === -1) {
		return value;
	}

	var inputId = match[1];
	var fieldId = parseInt(inputId, 10);

	var $fieldWrapper = $(`#gform_${formId} #field_${formId}_${fieldId}`);
	if ($fieldWrapper.length === 0) {
		return value;
	}

	var $rows = $fieldWrapper.find('.gfield_list_group');
	if ($rows.length === 0) {
		return value;
	}

	var nonBlankCount = 0;

	$rows.each(function () {
		var isNonBlank = false;

		$(this).find('input').each(function () {
			if ($(this).val().trim() !== '') {
				isNonBlank = true;
				return false;
			}
		});

		if (isNonBlank) {
			nonBlankCount++;
		}
	});

	return nonBlankCount;
});

$(document).ready(function () {
	// Recalculate when a list field is changed.
	$(document).on('change', '.gfield_list_group_item input', function () {
		var $group = $(this).parents('.gfield_list_group');
		var $container = $group.parents('.gfield_list_container');
		var formId = $container.closest('.gform_wrapper').attr('id').replace('gform_wrapper_', '');

		if ( !formId || typeof window.gf_global.gfcalc[formId] === 'undefined') {
			return;
		}

		var calcObject = window.gf_global.gfcalc[formId];

		calcObject.runCalcs(formId, calcObject.formulaFields);
	});
});
