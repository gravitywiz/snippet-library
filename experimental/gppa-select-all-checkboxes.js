/**
 * Gravity Perks // GP Populate Anything // Automatically Check Checkboxes
 * http://gravitywiz.com/documentation/gravity-forms-populate-anything
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 *.    3. This snippet is meant to be a starting point. You will need to update the selectors accordingly
 */

// Select all choices for field ID 1 on initial load
jQuery('#button_1_select_all').each(function() {
	gformToggleCheckboxes(this);
});

// Select all choices for field ID 2 whenever its choices are repopulated dynamically
jQuery(document).on('gppa_updated_batch_fields', function(event, formId, fieldIds) {
	if (formId != GFFORMID) {
		return;
	}

	jQuery('#button_2_select_all').each(function() {
		gformToggleCheckboxes(this);
	});
})
