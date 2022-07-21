<?php
/**
 * Gravity Perks // Limit Choices // Share Limit Between two Different Choices on the Same Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 */
// Update the "123" to your form ID and "4" to your field ID.
add_filter( 'gplc_choice_counts_123_4', function( $counts, $form, $field ) {
	// Update "First Choice" and "Second Choice" to the values of the two different choices.
	$counts['First Choice'] += $counts['Second Choice'];
	$counts['Second Choice'] = $counts['First Choice'];
	return $counts;
}, 10, 3 );
