/**
 * Gravity Perks // GP QR Code // Automatically Launch Scanner on Form Render
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * When using the QR scanner, automatically launch the scanner after the form has rendered.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
setTimeout( function() {
	$( '.gpqr-scanner-button' ).click();
} );
