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
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 3. Update the `landscapeRatio` variable per the inline instructions. 
 */
// Update "3/2" to the desired ratio for landscape images. Portraits will automatically have the ratio flipped (e.g. "2/3").
let landscapeRatio = 3/2;
let currentFile;
let currentFileDimensions;

gform.addAction( 'gpfup_before_upload', async function( formId, fieldId, file, up, gpfup ) {
	currentFile = file;
	currentFileDimensions = await getImageSize( currentFile.getNative() );
} );

gform.addFilter( 'gpfup_cropper_options', function( options, formId, fieldId ) {
	
	if (currentFileDimensions) {
		if ( typeof options.stencilProps === 'undefined' ) {
			options.stencilProps = {};
		}
		
		if ( currentFileDimensions.height > currentFileDimensions.width ) {
			options.stencilProps.aspectRatio = 1 / landscapeRatio;
		} else {
			options.stencilProps.aspectRatio = landscapeRatio;
		}
	}
	
	return options;
} );

function getImageSize( source ) {
	const img = new Image;

	return new Promise((resolve) => {
		img.onload = function() {
			resolve({
				width: img.width,
				height: img.height,
			})
		};

		if (typeof source !== 'string')  {
			img.src = URL.createObjectURL(source);
		} else {
			img.src = source;
		}
	});
}
