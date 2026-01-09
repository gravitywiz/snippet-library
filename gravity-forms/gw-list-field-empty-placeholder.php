<?php
/**
 * Gravity Wiz // Gravity Forms // List Field Empty Placeholder
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/31db165c2a604a8b8573247c9416de2f
 *
 * Adds a placeholder value (default: 'N/A') to any empty List field cells during form submission.
 * This ensures that empty list field values display consistently instead of being blank.
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
