<?php
/**
 * Gravity Wiz // Gravity Forms // Create an Extra Entry if Checkbox is Checked
 * https://gravitywiz.com/
 *
 * Create an additional entry identical to the first if a specified checkbox is checked.
 *
 * @see https://stackoverflow.com/a/69497108/227711
 */
// Update "123" to your form ID.
add_filter( 'gform_after_submission_123', function( $entry ) {
	// Update "4" to your Checkbox field ID. Assuming this is a single checkbox field, leave the "1" as it will target the first checkbox.
	if ( $entry['4.1'] ) {
		GFAPI::add_entry( $entry );
	}
}, 10 );
