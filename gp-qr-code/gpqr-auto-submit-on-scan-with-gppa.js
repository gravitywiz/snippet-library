/**
 * Gravity Perks // GP QR Code // Automatically Submit After Successful Scan (w/ Populate Anything)
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * You want to auto-submit the form after you've scanned a QR code but you're using Populate Anything
 * to populate other fields based on the value scanned. You need to wait for Populate Anything to
 * finish fetching and populating that data before the form can be submitted. This snippet handles
 * that logic for you.
 *
 * We recommend installing this snippet with our free Custom Javascript plugin:
 * https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addAction( 'gpqr_on_scan_success', function( decodedText, decodedResult, gpqrObj ) {
	$( document ).off( 'gppa_updated_batch_fields.gpqr' );
	$( document ).on( 'gppa_updated_batch_fields.gpqr', function( event, formId ) {
		if ( gpqrObj.formId == formId ) {
			setTimeout( function() {
				$( '#gform_{0}'.gformFormat( formId ) ).submit();
			} );
		}
	} );
} );
