<?php
/**
 * Gravity Wiz // Gravity Forms // List Field Empty Placeholder
 * https://gravitywiz.com/
 * 
 * Adds a placeholder value (default: 'N/A') to any empty List field cells during form submission.
 * This ensures that empty list field values display consistently instead of being blank.
 *
 * Instructions:
 * 
 * 1. Add this snippet to your site. See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/.
 * 2. Update the form ID in the filter hook from "27" to your form ID.
 * 3. Update the field IDs in the $field_ids array to match your List field IDs.
 * 4. Optionally, change the $placeholder_val to your preferred placeholder text.
 */
// Update "27" to your form ID.
add_filter( 'gform_pre_submission_filter_27', function ( $form ) {

	$field_ids       = array( 1 ); // List field IDs
	$placeholder_val = 'N/A';      // Change placeholder text if needed

	foreach ( $form['fields'] as &$field ) {

		if ( ! in_array( (int) $field->id, $field_ids, true ) || $field->type != 'list' ) {
			continue;
		}

		$input_name = 'input_' . $field->id;

		if ( empty( $_POST[ $input_name ] ) || ! is_array( $_POST[ $input_name ] ) ) {
			continue;
		}

		$columns_count = count( $field->choices );
		foreach ( $_POST[ $input_name ] as $index => $value ) {

			if ( trim( (string) $value ) === '' ) {
				$column_index                   = $index % $columns_count;
				$_POST[ $input_name ][ $index ] = $placeholder_val;
			}
		}
	}

	return $form;
} );
