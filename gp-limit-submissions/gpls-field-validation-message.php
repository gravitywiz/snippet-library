<?php
/**
 * Gravity Perks // Limit Submission // Change Validation Message for a Specific Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gpls_field_validation_message_123_4', function( $message ) {
	return 'Example validation message.';
} );
