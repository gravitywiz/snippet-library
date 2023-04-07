<?php
/**
 * Gravity Perks // Blocklist // Set Blocklist by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-blocklist/
 *
 * Video Instructions: https://www.loom.com/share/95b7192628774a3ebd0a0f47a4acd50a
 * 
 * Use this snippet to create a blocklist editor form. We recommend using [Populate Anything][1] to
 * populate WordPress' current blocklist into the field. The user can then edit the values and submit 
 * the form. This snippet will handle updating the blocklist option in the database.
 *
 * [1]: https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID.
add_action( 'gform_after_submission_123', function( $entry, $form ) {
	// Update "4" to the field ID whose value should be used to set the blocklist.
	update_option('disallowed_keys', rgar( $entry, 4 ) );
}, 10, 2 );
