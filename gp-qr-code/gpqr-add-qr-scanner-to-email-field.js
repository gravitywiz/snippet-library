/**
 * Gravity Perks // GP QR Code // Add QR Code Scanner Checkbox To Email Fields
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
window.gform.addFilter( 'gpqr_is_supported_field', function( isSupported, field ) {
	return isSupported || (field.type === 'email' || field.inputType === 'email');
}, 10, 'emails' );
