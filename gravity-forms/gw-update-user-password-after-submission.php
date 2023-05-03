<?php
/**
 * Gravity Wiz // Gravity Forms // Update User Password After Submission
 * https://gravitywiz.com/
 *
 * Updates the password for the user ID associated with the inputted email, allowing a custom password reset form.
 * 
 */

// Update "123" to your form ID, "4" to your "Password" field ID, and "5" to your "Confirm Password" field ID.
// If you're not using a "Confirm Password" field, change "$double_password_confirmation" from "true" to "false".
add_filter( 'gform_field_validation_123_5', function( $result, $value, $form ) {

    $double_password_confirmation = true;
    
    if( ! $double_password_confirmation ) {
    	return;
    }

    $master = rgpost( 'input_4' );
    if ( $result['is_valid'] && $value != $master ) {
        $result['is_valid'] = false;
        $result['message']  = 'Passwords do not match. Please try again.';
    }

	return $result;
}, 10, 4 );

// Update "123" to your form ID, "4" to your "Email" field ID, and "5) to your "Password" field ID.
// If you're using GP Nested Forms, set these to the Form and Field IDs of the child form that contains the email and password fields.
// If you're using GP Populate Anything, make sure the "value" for the user email field is set to "User Email".
add_action( 'gform_after_submission_123', function ( $entry, $form ) {

    $user = get_user_by( 'email', rgar( $entry, 4 ) );
    $user_id = $user->ID;

    wp_set_password( rgar( $entry, 5 ), $user_id );
}, 10, 2 );
