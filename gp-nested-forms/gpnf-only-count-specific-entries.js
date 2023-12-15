/**
 * Gravity Perks // GP Nested Forms // Only count entries that match certain criteria with `:count` merge tag modifier
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 2. Install https://github.com/gravitywiz/snippet-library/blob/master/experimental/gfjs-early-init-scripts.php
 * 3. Install accompanying `gpnf-only-count-specific-entries.php` PHP snippet
 * 4. Customize form ID, field ID(s), and value check
 */
window.gform.addFilter( 'gpnf_calc_entries', function(entries, match, fieldId, formId, self, formulaField) {
	// Only run this for Nested Form Field ID 1 in form ID 33.
	if (fieldId !== 1 || formId != 33) {
	}

	// Only change what entries are used for the calculation done in field ID 3
	if (formulaField.field_id != 3) {
		return entries;
	}

	// Create an empty collection of filtered entries that we can add entries to that match the criteria
	var filteredEntries = [];

	// Loop through each child entry and add them to the filterEntries array if field ID 1's value is "Second Choice"
	entries.forEach(function(childEntry) {
		if (childEntry[1].value === 'Second Choice') {
			filteredEntries.push(childEntry);
		}
	});

	return filteredEntries;
});
