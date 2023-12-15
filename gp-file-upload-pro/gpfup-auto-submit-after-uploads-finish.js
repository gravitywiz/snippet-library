/**
 * Gravity Perks // File Upload Pro // Auto Submit After Uploads Finish
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instruction Video: https://www.loom.com/share/6349c239eaa341998792ff6be9810e10
 */
gform.addAction( 'gpfup_uploader_ready', function( gpfup ) {
	gpfup.Uploader.bind( 'UploadComplete', function() {
		if ( document.hidden ) {
			jQuery( 'form#gform_GFFORMID' ).submit();	
		}
	} );
} );
