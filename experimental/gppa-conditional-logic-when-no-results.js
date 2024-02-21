/**
 * Gravity Perks // Populate Anything // Conditional Logic using a field that populates with no result
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */

gform.addAction('gform_post_conditional_logic_field_action', function (formId, action, targetId, defaultValues, isInit) {
    // replace 26 with the field ID of the field that gets dynamically populated with No Results
	var targetValue = $( '#input_GFFORMID_26 :selected' ).val();
	if ( targetValue === 'Check' ) {
        // replace 27 with the field ID of the field that needs to be displayed with conditional logic
		$( '#field_GFFORMID_27' ).show();
	}
});

