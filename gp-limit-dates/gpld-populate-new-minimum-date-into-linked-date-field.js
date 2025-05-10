/**
 * Gravity Perks // Limit Dates // Populate the New Minimum Date into Linked Date Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * When Field B's minimum date is dependent on the selected date in Field A,
 * automatically populate the minimum date into Field B.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
const sourceFieldId = 25; // Replace with the ID of the source field (Field A)

// Initialize field events on form render.
document.addEventListener( 'gform/post_render', (event) => {
	const formId = event.detail.formId;
	const $field = getSourceField(formId, sourceFieldId);
	triggerFieldEventsIfValueExists($field);
});

// Handle conditional logic changes.
gform.addAction(
	'gform_post_conditional_logic_field_action',
	(formId, action, targetId, defaultValues, isInit) => {
		const $field = getSourceField(formId, sourceFieldId);
		triggerFieldEventsIfValueExists($field);
	}
);

// Triggers input and change events on a field if it has a value.
const triggerFieldEventsIfValueExists = ($field) => {
	const value = $field.val();
	if (value) {
		requestAnimationFrame(() => {
			$field.trigger('input').trigger('change');
		});
	}
};

// Get  the source field based on form ID and field ID.
const getSourceField = (formId, fieldId) => {
	return jQuery(`#input_${formId}_${fieldId}`);
};

gform.addAction( 'gpld_after_set_min_date', function( $input, date ) {
	$input.datepicker( 'setDate', date );
} );
