/**
 * Gravity Perks // Populate Anything // Require a Minimum Character Count Before Triggering a Change
 * https://gravitywiz.comhttps://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/ddad3d1b44dc48048b2655f5c46c6a74
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
