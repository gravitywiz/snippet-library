/**
 * Gravity Perks // Auto List Field // Dynamic Row Labels for List Fields
 * https://gravitywiz.com/documentation/gravity-forms-auto-list-field/
 *
 * Count only non-blank rows in a List field.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter('gpalf_total_rows', function (totalRows, fieldId, formId) {
	var $fieldWrapper = $(`#gform_${formId} #field_${formId}_${fieldId}`);
	if ($fieldWrapper.length === 0) {
		return totalRows;
	}

	var $rows = $fieldWrapper.find('.gfield_list_group');
	if ($rows.length === 0) {
		return totalRows;
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