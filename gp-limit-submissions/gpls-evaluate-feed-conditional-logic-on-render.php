<?php
/**
 * Gravity Perks // Limit Submissions // Evaluate Feed Conditional Logic on Render
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 *
 * Use this snippet to evaluate a feed's conditional logic on render. This is useful if you're prepopulating a field
 * value that is used in the feed's conditional logic.
 *
 * NOTE: This will only work reliably with a single Limit Submission feed per form. If you need to use this with more
 * than one feed on the same form, reach out via support.
 */
// Update "123" to your form ID.
add_filter( 'gpls_should_enforce_on_render_123', function( $should_enforce, $form, $field_values, $gpls_enforce ) {

	if ( $should_enforce || ! $gpls_enforce->is_limit_reached( $field_values ) ) {
		return $should_enforce;
	}

	$feed = gp_limit_submissions()->get_feeds( $form['id'] )[0];

	return gp_limit_submissions()->is_feed_condition_met( $feed, GFAPI::get_form( $feed['form_id'] ), $field_values );
}, 10, 4 );
