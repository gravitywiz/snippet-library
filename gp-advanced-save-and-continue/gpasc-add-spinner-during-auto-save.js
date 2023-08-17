/**
 * Gravity Perks // Advanced Save and Continue // Add Spinner During Autosave
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 *
 * This snippet allows you to add a spinner during an auto save request and then remove it
 * once the request is complete.
 * 
 * Instructions:
 *  1. Add snippet to form using https://gravitywiz.com/gravity-forms-custom-javascript/
 *  2. Profit.
 */

gform.addAction( 'gpasc_auto_save_started', function( formId, gpasc ) {
	var spinnerTarget = function() {
		return $( '.gform_save_link' );
	}
	gform.addFilter( 'gform_spinner_target_elem', spinnerTarget, 10, 'gpasc_spinner_target_elem' );
	gformAddSpinner( formId );
	gform.removeFilter( 'gform_spinner_target_elem', 10, 'gpasc_spinner_target_elem' );
} );

gform.addAction( 'gpasc_auto_save_finished', function( formId ) {
	$( '#gform_ajax_spinner_' + formId ).remove();
} );
