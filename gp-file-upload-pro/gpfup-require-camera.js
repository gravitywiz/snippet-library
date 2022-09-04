/**
 * Gravity Perks // File Upload Pro // Require Camera for Uploads
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Want to ensure that the user is uploaded a fresh image taken directly from their camera? 
 * This snippet will force use of the camera and prevent selecting an existing image on
 * mobile devices.
 */
gform.addAction( 'gpfup_uploader_ready', function( gpfup ) {
	gpfup.Uploader.bind( 'PostInit', function() {
		$( gpfup.$field ).find( 'input[type="file"]' ).attr( 'capture', 'camera' );
	}, gpfup );
} );
