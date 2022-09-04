/**
 * Gravity Perks // File Upload Pro // Require Camera for Uploads
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Want to ensure that the user is uploaded a fresh image taken directly from their camera? 
 * This snippet will force use of the camera and prevent selecting an existing image on
 * mobile devices.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
var $addEntry;

$( '#gform_GFFORMID' ).find( '.gpnf-add-entry' ).on( 'click', function() {
	$addEntry = $( this );
} );

$( '#gform_GFFORMID' ).on( 'click', '.gpnf-row-actions .edit-button', function() {
	$addEntry = null;
} );

$( document ).on( 'gform_confirmation_loaded', function( event, formId ) {
	if ( $addEntry && formId == $addEntry.data( 'nestedformid' ) ) {
		$addEntry.click();
	}
} );
 */
gform.addAction( 'gpfup_uploader_ready', function( gpfup ) {
	gpfup.Uploader.bind( 'PostInit', function() {
		$( gpfup.$field ).find( 'input[type="file"]' ).attr( 'capture', 'camera' );
	}, gpfup );
} );
