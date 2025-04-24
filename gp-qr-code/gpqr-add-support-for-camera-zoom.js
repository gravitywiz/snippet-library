/**
 * Gravity Perks // GP QR Code // Add Support for Camera Zoom
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
window.gform.addFilter( 'gpqr_scanner_config', function( config, instance, formats ) {
	config['showZoomSliderIfSupported'] = true;
	return config;
} );
