<?php
/**
 * Gravity Perks // Limit Submissions // Change the "Submission Limit" Reached Message For a Specific Form
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */
// Update "123" to the form ID
add_filter( 'gpls_limit_message_123', function( $message, $form, $gpls_enforce_instance ) {
	return 'No more submissions are accepted at this time.';
}, 10, 3 );
