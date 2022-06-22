/**
 * Gravity Perks // File Upload Pro // Disable Submit During Upload
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Use this snippet alongside our free [Gravity Forms Custom JavaScript][1] plugin to disable 
 * the submit button while files are being uploaded.
 *
 * [1]: https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addAction( 'gpfup_uploader_ready', function( gpfup ) {
	gpfup.Uploader.bind( 'UploadFile', function() {
		$( '.gform_button' ).prop( 'disabled', true );
	} );
	gpfup.Uploader.bind( 'UploadComplete', function() {
		$( '.gform_button' ).prop( 'disabled', false );
	} );
} );
