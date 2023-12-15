/**
 * Gravity Perks // File Upload Pro // Scale Uploaded Images
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Scale uploaded images instead of cropping, based on dimensions set in the cropping settings.
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
gform.addFilter( 'gpfup_image_loader_options', function( options ) {
	options.crop = false;

	return options;
} );
