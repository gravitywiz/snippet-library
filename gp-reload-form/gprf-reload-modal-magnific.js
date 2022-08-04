/**
 * Gravity Perks // Reload Form // Reload Form in Magnific Modal
 * https://gravitywiz.com/automatically-reload-gravity-form-modal-closed/
 * 
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 2. Two examples are included below. Remove the example that does not match your use case.
 */
// Example #1 – Initialize a new Magnific Popup that will automatically reload the form when the modal is closed.
// Update ".open-popup" to the selector for the element that will open the modal.
$( '.open-popup' ).magnificPopup( {
	type:'inline',
	callbacks: {
		close: function() {
			// Update "82" to the ID of the form within the modal.
			var gwrf = window.gwrf_82;
			if( typeof gwrf != 'undefined' ) {
				gwrf.reloadForm();
			}
		}
	}
} );

// Example #2 – Automatically reload a form when a previously configured Magnific popup is closed.
// Update ".open-popup" to the selector for the element that has been previoulsy been initialized with Magnific.
$( '.open-popup' ).on( 'mfpClose', function() {
	// Update "82" to the ID of the form within the modal.
	var gwrf = window.gwrf_82;
	if ( typeof gwrf != 'undefined' ) {
		gwrf.reloadForm();
	}
} );
