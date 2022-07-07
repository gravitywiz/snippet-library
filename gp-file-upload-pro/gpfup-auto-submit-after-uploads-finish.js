/**
 * Gravity Perks // File Upload Pro // Auto Submit After Uploads Finish
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 */
gform.addAction( 'gpfup_uploader_ready', function( gpfup ) {
	gpfup.Uploader.bind( 'UploadComplete', function() {
		if ( document.hidden ) {
			jQuery( 'form#gform_GFFORMID' ).submit();	
		}
	} );
} );
