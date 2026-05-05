/**
 * Gravity Perks // GP Advanced Calculations // Calculation Logic on Text Field
 * https://gravitywiz.com/documentation/gravity-forms-advanced-calculations
 *
 * Instruction Video: https://www.loom.com/share/db50e407e0634748a36b189b63e0bb92
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
// Update these variables to match your calculation and target text field ID
var calc_field_id   = 4;
var target_field_id = 3;

// Update this config map for calculation results to text values
var config_map = {
	1: 'Tiny',
	2: 'Medium',
	3: 'Big',
	0: 'None'
};

gform.addFilter('gform_calculation_result', function(result, formulaField) {
	if (formulaField.field_id != calc_field_id) {
		return result;
	}

	var value = config_map[result] ? config_map[result] : '';

	var $el = $('#input_GFFORMID_' + target_field_id);
	if (!$el.length) return result;

	$el.val(value).trigger('change');

	return result;
});
