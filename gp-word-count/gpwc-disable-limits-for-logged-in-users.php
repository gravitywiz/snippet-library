<?php
/**
 * Gravity Perks // Word Count // Disable Limits for Logged-in Users
 * https://gravitywiz.com/documentation/gravity-forms-word-count/
 */
// Update "123" to your form ID.
add_action( 'gform_pre_render_123', 'gpwc_remove_limits_for_logged_in_user' );
add_action( 'gform_pre_process_123', 'gpwc_remove_limits_for_logged_in_user' );
function gpwc_remove_limits_for_logged_in_user( $form ) {

	if ( ! is_user_logged_in() ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {
		$field->gwwordcount_min_word_count = null;
		$field->gwwordcount_max_word_count = null;
	}

	return $form;
}
