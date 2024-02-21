/**
 * Gravity Perks // File Upload Pro // Set Minimum File Size
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Specify a minimum size per file.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
window.gform.addAction( 'gpfup_before_upload', (formId, fieldId, file, up, gpfupInstance) => {
	// Specify min size per file in megabytes.
	var minFileSize = 1;

	if ( file.size < minFileSize * 1000000 ) {
		file.type = 'application/octet-stream'; // Prevent image processing

		gpfupInstance.handleFileError( up, file, {
			code: 'too_little_file',
			message: 'File size must be at least ' + minFileSize + 'MB.',
		} );

		up.stop();
		up.start();
	}
} );
