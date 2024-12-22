/**
 * Gravity Wiz // GP Populate Anything // Trigger GPPA update on Image Choice Wrapper Click.
 * http://gravitywiz.com/documentation/gravity-forms-populate-anything
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
// If Image Choice's Image Wrapper is clicked, make sure to trigger the `change.gppa` event.
window.gform.addAction(
	'gform_input_change',
	(elem , formId, fieldId) => {
		if (
			!$(elem)
				.parent()
				.hasClass('gfield-image-choice-wrapper-inner')
		) {
			return;
		}
		$(elem).trigger('change.gppa');
	}
);