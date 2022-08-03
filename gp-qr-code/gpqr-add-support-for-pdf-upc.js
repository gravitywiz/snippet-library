/**
 * Gravity Perks // GP QR Code // Add Support for PDF_417 and UPC_A
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
window.gform.addFilter( 'gpqr_scanner_config', function( config, instance, formats ) {
	config['formatsToSupport'] = [ formats.QR_CODE, formats.PDF_417, formats.UPC_A ];
	return config;
} );
