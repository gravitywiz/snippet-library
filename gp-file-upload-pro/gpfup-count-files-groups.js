/**
 * Gravity Perks // File Upload Pro // Count Files in Groups
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instruction Video: https://www.loom.com/share/2328e327125844bebdcabf7c9baaabca
 */
var formId = GFFORMID;

// Map count field IDs to arrays of file upload field IDs
var countMapping = {
	5: [1, 3, 4],    // Number Field ID 5 counts files from File Upload Field IDs 1, 3, 4
	10: [7, 8, 9]    // Number Field ID 10 counts files from File Upload Field IDs 7, 8, 9
	// Add more mappings if needed
};

// Find all GPFUP keys for the form
var gpfupInstances = Object.keys(window).filter(function (key) {
	return key.startsWith('GPFUP_' + formId + '_');
});

if (!gpfupInstances.length) {
	return;
}

// Build reverse lookup: uploadFieldID => associated countFieldIDs
var uploadToCountMap = {};
Object.entries(countMapping).forEach(function ([countFieldID, uploadFieldIDs]) {
	uploadFieldIDs.forEach(function (uploadFieldID) {
		if (!uploadToCountMap[uploadFieldID]) {
			uploadToCountMap[uploadFieldID] = [];
		}
		uploadToCountMap[uploadFieldID].push(parseInt(countFieldID));
	});
});

// Function to update all relevant count fields
function updateAllCountFields() {
	Object.entries(countMapping).forEach(function ([countFieldID, uploadFieldIDs]) {
		var total = uploadFieldIDs.reduce(function (sum, uploadFieldID) {
			var key = 'GPFUP_' + formId + '_' + uploadFieldID;
			var store = window[key] && window[key].$store;
			return sum + (store ? (store.state.files.length || 0) : 0);
		}, 0);

		var selector = '#input_' + formId + '_' + countFieldID;
		jQuery(selector).val(total).change();
	});
}

// Subscribe to relevant GPFUP fields
gpfupInstances.forEach(function (key) {
	var parts = key.split('_');
	var fieldID = parseInt(parts[2]); // GPFUP_formId_fieldId
	var store = window[key].$store;

	if (uploadToCountMap[fieldID]) {
		store.subscribe(function (mutation, state) {
			if (mutation.type === 'SET_FILES') {
				updateAllCountFields();
			}
		});
	}
});

// Initial count on load
updateAllCountFields();
