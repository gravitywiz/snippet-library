/**
 * Gravity Perks // Copy Cat // Copy Comma-delimited Value to Checkbox Field
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 *
 * Use Copy Cat to check the checkboxes in a Checkbox field based on a comma-delimited value
 * from a single-value field (like a Single Line Text field).
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter( 'gpcc_copied_value', function( sourceValues, $targetElem, field ) {
  // Update "3" to your Checkbox field ID.
	if ( field.target == 3 ) {
		sourceValues = sourceValues[0].split( ', ' );
	}
	return sourceValues;
} );
