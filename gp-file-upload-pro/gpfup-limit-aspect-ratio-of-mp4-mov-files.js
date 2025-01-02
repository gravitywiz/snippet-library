/**
 * Gravity Perks // File Upload Pro // Limit Aspect Ratio of MP4/MOV Files
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Experimental Snippet 🧪
 *
 * Instruction Video: https://www.loom.com/share/b05a322bbf204e49b23ae366a123be96
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addAction('gpfup_before_upload', (formId, fieldId, file, up, gpfupInstance) => {
	// Update "4:3" to the desired video aspect ratio.
	const targetAspectRatio = '4:3';

	const [width, height] = targetAspectRatio.split(':').map(Number);
	const numericalAspectRatio = width / height;

	const videoMimeTypes = ['video/mp4', 'video/quicktime'];

	if (videoMimeTypes.indexOf(file.type) !== -1) {
		var fileURL = URL.createObjectURL(file.getNative());
		var vid = document.createElement('video');
		vid.src = fileURL;

		// check the video aspect ratio
		vid.onloadedmetadata = function () {
			const aspectRatio = this.videoWidth / this.videoHeight;

			if (numericalAspectRatio != aspectRatio) {
				gpfupInstance.handleFileError(up, file, {
					code: 'does_not_meet_aspect_ratio',
					message: `Video duration must be of ${targetAspectRatio} aspect ratio.`,
				});
			}	
		};
	}
});
