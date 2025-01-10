/**
 * Gravity Perks // File Upload Pro // Flip Aspect Ratio Based on Dimensions
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instructions:
 *
 * 1. Configure your field by selecting the "Enable Cropping" field setting.
 *    DO NOT set the "Aspect Ratio" setting.
 *
 * 2. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 3. Update the `landscapeRatio` variable per the inline instructions.
 */
// Update "3/2" to the desired ratio for landscape images. Portraits will automatically have the ratio flipped (e.g. "2/3").
let currentFile;
let currentFileDimensions;
let landscapeRatio = 3/2;

gform.addFilter( 'gpfup_cropper_options', function( options, formId, fieldId ) {

	if ( typeof options.stencilProps === 'undefined' ) {
		options.stencilProps = { 'aspectRatio': landscapeRatio };
	}

	if ( typeof options.defaultSize === 'undefined' ) {
		return options;
	}

	if ( options.defaultSize.height > options.defaultSize.width ) {
		options.stencilProps.aspectRatio = 1 / landscapeRatio;
	} else {
		options.stencilProps.aspectRatio = landscapeRatio;
	}

	return options;
} );
