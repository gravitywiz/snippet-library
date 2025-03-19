<?php
/**
 * Gravity Wiz // Gravity Forms // Capture List Field Column as Comma-delimited List
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 */
// Update "123" to your form ID.
add_action( 'gform_pre_submission_123', function( $form ) {

	$list_field_id   = 4; // Update to your List field ID.
	$column_number   = 3; // Update to the column number you would like to fetch values from.
	$target_field_id = 5; // Update to the ID of the field to which you would like to capture values.

	$list_field = GFAPI::get_field( $form, $list_field_id );
	$rows       = $list_field->create_list_array( $_POST[ "input_{$list_field_id}" ] );
	$values     = array();

	if ( is_array( $rows[0] ) ) {
		foreach ( $rows as $row ) {
			$row      = array_values( $row );
			$values[] = $row[ $column_number - 1 ];
		}
	} else {
		foreach ( $rows as $row ) {
			$values[] = $row;
		}
	}

	$_POST[ "input_{$target_field_id}" ] = implode( ',', $values );

} );
