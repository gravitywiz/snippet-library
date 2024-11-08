<?php
/**
 * Gravity Perks // Limit Submissions // Use the latest user entry to replace merge tag
 * variable for the limit message, when rule is set to `Each User`.
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */

//Update string `45` to your form ID.
add_filter( 'gpls_limit_message_45', function( $message, $form ) {
	if ( ! rgpost( 'gform_submit' ) ) {
		$search_criteria['field_filters'] = array(
			array(
				'key'   => 'created_by',
				'value' => get_current_user_id(),
			),
		);
		$total_count                      = 0;
		$results                          = GFAPI::get_entries( $form['id'], $search_criteria, array(), array(), $total_count );
		if ( $total_count > 0 ) {
			$message = GFCommon::replace_variables( $message, $form, $results[0], false, false, false, 'html' );
		}
	}

	return $message;
}, 10, 2 );
