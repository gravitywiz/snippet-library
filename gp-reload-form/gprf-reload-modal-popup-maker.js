/**
 * Gravity Perks // Reload Form // Reload Form in Popup Maker
 * https://gravitywiz.com/automatically-reload-gravity-form-modal-closed/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
// Update "13256" to the ID of your modal.
$( '#pum-13256' ).on( 'pumAfterClose', function() {
	// Update "82" to the ID of the form within the modal.
	var gwrf = window.gwrf_82;
	if( typeof gwrf != 'undefined' ) {
		gwrf.reloadForm();
	}
} );
