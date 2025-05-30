/**
 * Gravity Wiz // Gravity Forms // Disable Submission when Pressing Enter
 * https://gravitywiz.com/disable-submission-when-pressing-enter-for-gravity-forms/
 *
 * Instructions:
 * 1. Install our free Custom Javascript for Gravity Forms plugin.
 * Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 * 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
document.addEventListener( 'keydown', function(e) {
	var isGravityForm = e.target.closest( '.gform_wrapper' ) !== null;
	var code = e.keyCode || e.which;
	if ( code == 13 && isGravityForm ) {
		var isTextarea = e.target.tagName.toLowerCase() === 'textarea';
		var isSubmitOrButton = jQuery(e.target).is('input[type="submit"], input[type="button"]');

		// Allow default for submit/buttons.
		if (isSubmitOrButton) {
			return;
		}

		// Allow Shift+Enter in textarea for line breaks.
		if (isTextarea && e.shiftKey) {
			return;
		}

		// Block everything else.
		e.stopImmediatePropagation();
		e.preventDefault();
	}
}, true );
