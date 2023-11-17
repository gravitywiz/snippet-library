<?php
/**
 * Gravity Perks // Limit Submissions // Include Field Value in Validation Message
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */
// Update "123" to your form ID and "4" to your field ID. Duplicate this line and update the field ID for each field to which this should apply.
add_filter( 'gpls_field_validation_message_123_4', 'gpls_include_field_value_in_validation_message', 10, 2 );
add_filter( 'gpls_field_validation_message_123_5', 'gpls_include_field_value_in_validation_message', 10, 2 );

function gpls_include_field_value_in_validation_message( $message, $gpls_enforce ) {
	$bits = explode( '_', current_filter() );
	if ( count( $bits ) === 6 ) {
		$field_id = array_pop( $bits );
    // Update your validation message as desired.
		$message  = sprintf( 'You have entered "%s" in this field before.', rgpost( "input_{$field_id}" ) );
	}
	return $message;
}
