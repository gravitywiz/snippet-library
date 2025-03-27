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
window.gform.addFilter( 'gpnf_sorted_entries', function( entries, formId, fieldId, gpnf ) {
	// Replace "3" with the field ID of the field you would like to sort by.
	// JavaScript provides several ways to sort arrays, including different sorting functions like localeCompare(), numeric sorting, and custom sorting based on object properties. Use the one that best fits your needs.
	return entries.sort((a, b) => a["3"].label.localeCompare(b["3"].label));
}, 10, 'emails' );

