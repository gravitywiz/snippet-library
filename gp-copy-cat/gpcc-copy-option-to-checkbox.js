/**
 * Gravity Perks // Copy Cat // Copy Option to Checkbox
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 *
 * Use this snippet to copy option field value to a Checkbox field.
 *
 * Screenshot: https://gwiz.io/3wWlUts
 *
 * Instructions:
 * 
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter('gpcc_copied_value', function(value, $elem, data) {
	if (!data || !data.sourceFormId || !data.source) {
		return value;
	}

	const sourcefieldId = '#field_' + data.sourceFormId + '_' + data.source;
	const $sourceField = $(sourcefieldId);

	if ($sourceField.length && $sourceField.hasClass('gfield--type-option')) {
		if (Array.isArray(value)) {
			value = value.map(item => typeof item === 'string' ? item.split('|')[0] : item);
		}
	}

	return value;
});
