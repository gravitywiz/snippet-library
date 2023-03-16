/**
 * Gravity Perks // Page Tranisitions // Uncheck Radio Buttons When Navigating to Previous Page
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 * 
 * Uncheck Radio Buttons on the previous page when navigating backwards through a form. This will
 * require the user to make a fresh selection as the progress through the form again.
 *
 * Only works when Soft Validation is enabled.
 * 
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
 $( '#gform_GFFORMID' ).on( 'softValidationPageLoad.gppt', function( event, newPage, oldPage ) {
	if ( oldPage > newPage ) {
		$( '#gform_page_GFFORMID_' + oldPage ).find( 'input[type="radio"]' ).prop( 'checked', false );
	}
} );
