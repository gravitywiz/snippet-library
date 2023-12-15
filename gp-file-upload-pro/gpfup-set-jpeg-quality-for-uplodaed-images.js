/**
 * Gravity Perks // File Upload Pro // Set JPEG Quality for all Uploaded Images
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addFilter( 'gpfup_jpeg_quality', function () {
	// Update '1' to the desired quality. 1 represents 100%.
	return 1;
} );
