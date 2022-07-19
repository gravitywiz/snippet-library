/**
 * Gravity Perks // File Upload Pro // Set Total Maximum File Size
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Specify a total max file size (the maximum size accepted collectively for all files in a given field).
 * If a new file is uploaded that exceeds the collective max file size, a validation error will be
 * displayed for that file.
 *
 * Instructions:
 *  1. Install our free Custom Javascript for Gravity Forms plugin.
 *     Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *  2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addAction( 'gpfup_before_upload', (formId, fieldId, file, up, gpfupInstance) => {
	// Specify max total size of all files combined in megabytes.
	var maxTotalSize = 1;
	var totalSize    = 0;
	gpfupInstance.$store.getters.allFiles.forEach( function( file ) {
		totalSize += file.size;
	} );
	if ( totalSize > maxTotalSize * 1000000 ) {
		gpfupInstance.handleFileError( up, file, {
			code: 'too_much_file',
			message: 'Max total file size of ' + maxTotalSize + 'MB has reached.',
		} );
	}
} );
