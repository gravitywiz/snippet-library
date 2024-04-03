/**
 * Gravity Wiz // Gravity Forms // Evaluate if All Checkboxes are Checked.
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/5c55fe7fa415428aa48b249d6ead071f
 *
 * * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
// Update "1" and "2" to the checkbox field id and hidden field id on your form
var checkboxFieldId = 1;
var hiddenFieldId   = 2;

gform.addFilter( 'gppa_should_trigger_change', function( triggerChange, formId, inputId, $el, event ) {
	var totalCheckboxes   = $('#input_GFFORMID_' + checkboxFieldId + ' input[type="checkbox"]').length;
	var checkedCheckboxes = $('#input_GFFORMID_' + checkboxFieldId + ' input[type="checkbox"]:checked').length;

	// If all checkboxes are checked, set Hidden Field to 1, else set it to 0
	if (totalCheckboxes === checkedCheckboxes) {
		$('#input_GFFORMID_' + hiddenFieldId).val(1);
	} else {
		$('#input_GFFORMID_' + hiddenFieldId).val(0);
	}

	// Trigger conditional logic re-eval
	$(document).trigger('gform_post_render', [GFFORMID, 1]);

	return triggerChange;
});
