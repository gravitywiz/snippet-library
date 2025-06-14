/**
 * Gravity Perks // File Upload Pro // Count Files in Groups
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instruction Video: https://www.loom.com/share/2328e327125844bebdcabf7c9baaabca
 */
const formId = GFFORMID;

// Map count field IDs to arrays of file upload field IDs
const countMapping = {
	5: [1, 3, 4],    // Number Field ID 5 counts files from File Upload Field IDs 1, 3, 4
	10: [7, 8, 9]    // Number Field ID 10 counts files from File Upload Field IDs 7, 8, 9
	// Add more mappings as needed: countFieldID: [uploadFieldID1, uploadFieldID2, ...]
};

// Find all GPFUP keys for the form
var gpfupInstances = Object.keys(window).filter(function (key) {
	return key.startsWith('GPFUP_' + formId + '_');
});

// Build reverse lookup: uploadFieldID => associated countFieldIDs
const uploadToCountMap = {};
Object.entries(countMapping).forEach(([countFieldID, uploadFieldIDs]) => {
	uploadFieldIDs.forEach((uploadFieldID) => {
		if (!uploadToCountMap[uploadFieldID]) {
			uploadToCountMap[uploadFieldID] = [];
		}
		uploadToCountMap[uploadFieldID].push(parseInt(countFieldID));
	});
});

// Function to update all relevant count fields
function updateCountFields(countFieldIDs = Object.keys(countMapping)) {
	countFieldIDs.forEach((countFieldID) => {
		const uploadFieldIDs = countMapping[countFieldID];
		const total = uploadFieldIDs.reduce((sum, uploadFieldID) => {
			const key = 'GPFUP_' + formId + '_' + uploadFieldID;
			const store = window[key]?.$store;
			return sum + (store ? (store.state.files.length || 0) : 0);
		}, 0);

		const selector = '#input_' + formId + '_' + countFieldID;
		const $field = jQuery(selector);
		if ($field.length) {
			$field.val(total).change();
		}
	});
}

if (gpfupInstances.length) {
	// Subscribe to relevant GPFUP fields
	gpfupInstances.forEach((key) => {
		const parts = key.split('_');
		const fieldID = parseInt(parts[2]); // GPFUP_formId_fieldId
		const store = window[key].$store;

		if (uploadToCountMap[fieldID]) {
			store.subscribe((mutation, state) => {
				if (mutation.type === 'SET_FILES') {
					updateCountFields(uploadToCountMap[fieldID]);
				}
			});
		}
	});
}

// Initial count on load for all fields
updateCountFields();
