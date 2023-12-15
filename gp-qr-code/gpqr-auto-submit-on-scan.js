/**
 * Gravity Perks // GP QR Code // Automatically Submit After Successful Scan
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * When using the QR scanner, automatically submit the form after a successful scan.
 *
 * We recommend installing this snippet with our free Custom Javascript plugin:
 * https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addAction( 'gpqr_on_scan_success', function( decodedText, decodedResult, gpqr ) {
	gpqr.$input.parents( 'form' ).submit();
} );
