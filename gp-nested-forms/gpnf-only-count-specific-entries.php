<?php
/**
 * Gravity Perks // GP Nested Forms // Only count entries that match certain criteria with `:count` merge tag modifier
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms
 *
 * Instructions:
 *
 * 1. Install this snippet per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 * 2. Install accompanying `gpnf-only-count-specific-entries.js` JavaScript snippet
 * 3. Customize form ID, field ID(s), and value check
 */
add_filter( 'gpnf_calc_entries_PARENTFORMID_NESTEDFORMFIELDID', function ( $entries, $match, $nested_form_field, $form, $formula_field ) {
	// Only change what entries are used for the calculation done in field ID 3
	if ( rgar( $formula_field, 'id' ) != 3 ) {
		return $entries;
	}

	// Create an empty collection of filtered entries that we can add entries to that match the criteria
	$filtered_entries = array();

	// Loop through each child entry and add them to the filterEntries array if field ID 1's value is "Second Choice"
	foreach ( $entries as $child_entry ) {
		if ( $child_entry[1] === 'Second Choice' ) {
			$filtered_entries[] = $child_entry;
		}
	}

	return $filtered_entries;
}, 10, 5 );
