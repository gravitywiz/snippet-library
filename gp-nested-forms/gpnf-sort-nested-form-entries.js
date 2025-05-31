/**
 * WARNING! THIS SNIPPET IS DEPRECATED. 🚧
 * Unified JS and PHP snippet is available here: https://github.com/gravitywiz/snippet-library/blob/master/gp-nested-forms/gpnf-sort-nested-form-entries.php
 */
/**
 * Gravity Perks // Nested Forms // Sort Nested Form Entries
 *
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
// Change this value to the field ID of the child form you want to sort by.
const sortByFieldId = "3";
window.gform.addFilter('gpnf_sorted_entries', function (entries, formId, fieldId, gpnf) {
	// Check if entries exist and have the specified field.
	if ( !entries || !entries.length || !entries[0][sortByFieldId]) {
		console.warn(`GPNF Sort: Field ID ${sortByFieldId} not found in entries or entries are empty. Returning unsorted entries.`);
		return entries;
	}

	// Sort entries by the specified field's label.
	return entries.sort((a, b) => {
		if ( !a[sortByFieldId] || !a[sortByFieldId].label) return 1;
		if ( !b[sortByFieldId] || !b[sortByFieldId].label) return -1;
		return a[sortByFieldId].label.localeCompare(b[sortByFieldId].label);
	});
});

