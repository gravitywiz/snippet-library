/**
 * Gravity Perks // File Upload Pro // Include Image Metadata/EXIF on all Uploaded images
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addFilter('gpfup_strip_image_metadata', function () {
	return false;
} );
