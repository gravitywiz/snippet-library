<?php
/**
 * Gravity Perks // Limit Choices // Override Limit of Specific Choice
 * https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 */
// Update "123" to the form ID and "4" to the field ID.
add_filter( 'gplc_choice_limit_123_4', function( $limit, $choice ) {
	if ( $choice['value'] === 'First Choice' ) {
		// Update "1" to the desired limit.
		$limit = 1;
	}
	return $limit;
}, 10, 2 );
