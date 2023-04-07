<?php
/**
 * Gravity Perks // Blocklist // Set Blocklist by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-blocklist/
 *
 * Video Instructions: 
 * 
 * Use this snippet to create a blocklist editor form.
 */
// Update "123" to your form ID.
add_action( 'gform_after_submission_123', function( $entry, $form ) {
	// Update "4" to the field ID whose value should be used to set the blocklist.
	update_option('disallowed_keys', rgar( $entry, 4 ) );
}, 10, 2 );
