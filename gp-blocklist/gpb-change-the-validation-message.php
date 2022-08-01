<?php
/**
 * Gravity Perks // Blocklist // Change the Validation Message
 * https://gravitywiz.com/documentation/gravity-forms-blocklist/
 */
add_filter( 'gpb_validation_message', function( $validation_message ) {
	// Specify the message you would like to use.
	return 'YOUR MESSAGE';
});
