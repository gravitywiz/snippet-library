<?php
/**
 * Gravity Perks // GP Google Sheets // Force field value to be added to the sheet as a string
 *
 * In some situations, there may be numbers that have leading 0's, such as zip codes. By default, these will be converted
 * to numbers in Google Sheets and the leading 0's will be removed.
 *
 * This snippet casts the value to a string before it is sent to Google Sheets, which will preserve the leading 0's.
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Update the FORMID and FIELDID accordingly.
 */
add_filter( 'gpgs_row_value_FORMID_FIELDID', function( $value, $form_id, $field_id, $entry, $original_value ) {
	return (string) $original_value;
}, 10, 5 );
