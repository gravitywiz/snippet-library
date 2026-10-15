/**
 * Gravity Perks // File Upload Pro // Center Initial Crop
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instructions:
 *
 * 1. Enable Cropping on your File Upload Pro field.
 * 2. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
window.gform.addFilter( 'gpfup_cropper_options', function( options ) {
	const previousPosition = options.defaultPosition;
	const previousSize = options.defaultSize;

	options.defaultPosition = function( { imageSize, coordinates } ) {
		// Preserve the position when reopening an image that has already been cropped.
		if (
			previousPosition &&
			(
				previousPosition.left !== 0 ||
				previousPosition.top !== 0 ||
				(
					previousSize &&
					(
						previousSize.width !== imageSize.width ||
						previousSize.height !== imageSize.height
					)
				)
			)
		) {
			return previousPosition;
		}

		return {
			left: ( imageSize.width - coordinates.width ) / 2,
			top: ( imageSize.height - coordinates.height ) / 2,
		};
	};

	return options;
} );
