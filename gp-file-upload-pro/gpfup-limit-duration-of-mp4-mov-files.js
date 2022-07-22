/**
 * Gravity Perks // File Upload Pro // Limit Duration of MP4/MOV Files
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addAction('gpfup_before_upload', (formId, fieldId, file, up, gpfupInstance) => {
	// Update "10" to the desired number of seconds.
	var maxDurationInSeconds = 10;

	const videoMimeTypes = ['video/mp4', 'video/quicktime'];

	if (videoMimeTypes.indexOf(file.type) !== -1) {
		var fileURL = URL.createObjectURL(file.getNative());
		var vid = document.createElement('video');
		vid.src = fileURL;

		// wait for duration to change from NaN to the actual duration
		vid.ondurationchange = function () {
			if (this.duration > maxDurationInSeconds) {
				gpfupInstance.handleFileError(up, file, {
					code: 'does_not_meet_max_duration',
					message: 'Video duration cannot exceed  ' + maxDurationInSeconds + ' seconds.',
				});
			}
		};
	}
});
