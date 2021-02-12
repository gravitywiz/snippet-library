<?php
/**
 * Gravity Perks // GP Limit Submissions // Top Level Validation Message for Hidden Fields
 * http://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 *
 * If a Limit Submissions feed returns a validation error and is based on the field values of a hidden field, the
 * feed's Limit Message will be used as the form's top level validation message.
 */
add_filter( 'gform_validation_message', function ( $message, $form ) {

	$has_other_error  = false;
	$gpls_error_field = false;

	foreach ( $form['fields'] as $field ) {

		if ( ! $field->failed_validation ) {
			continue;
		}

		if ( ( $field->visibility === 'hidden' || $field->get_input_type() === 'hidden' ) && strpos( $field->validation_message, 'gpls-limit-message' ) ) {
			$gpls_error_field = $field;
		} else {
			$has_other_error = true;
		}
	}

	if ( $gpls_error_field && ! $has_other_error ) {
		$message = sprintf( "<div class='validation_error'>%s</div>", $gpls_error_field->validation_message );
	}

	return $message;
}, 10, 2 );
