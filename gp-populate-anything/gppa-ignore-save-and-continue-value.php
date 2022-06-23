<?php
/**
 * Gravity Perks // Populate Anything // Ignore Save & Continue Value
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gform_incomplete_submission_post_get', function( $submission_json, $resume_token, $form ) {
	// Update "123" to your form ID.
	if ( $form['id'] == 123 ) {
		$submission = json_decode( $submission_json, ARRAY_A );
		// Update "4" to the ID of the field that should *not* be repopulated from Save & Continue.
		$submission['submitted_values'][4] = '';
		$submission_json                   = json_encode( $submission );
	}
	return $submission_json;
}, 10, 3 );
