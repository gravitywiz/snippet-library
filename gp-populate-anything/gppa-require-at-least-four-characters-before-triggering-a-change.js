/**
 * Gravity Perks // Populate Anything // Require Input to Have at Least Four Characters Before Triggering a Change
 * https://gravitywiz.comhttps://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
window.gform.addFilter('gppa_should_trigger_change', function( triggerChange, formId, inputId, $el, event ) {
	// Update "1" to the field ID.
	if ( inputId == 1 ) {
		// Require length for input 1 to be at least 4.
		if ( event.currentTarget.value.length < 4 ) {
			return false;
		}
	}
	
	return triggerChange;
} );
