/**
 * Gravity Perks // GP QR Code // Move Focus to Next Field After QR Scan
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * When using the QR scanner, automatically move the focus to next field after a QR code is successfully scanned.
 */
gform.addAction( 'gpqr_on_scan_success', function( decodedText, decodedResult, gpqr ) {
	gpqr.$input.parents( '.gfield' ).next( '.gfield:visible' ).find( 'input, select, textarea' ).focus();
} );
