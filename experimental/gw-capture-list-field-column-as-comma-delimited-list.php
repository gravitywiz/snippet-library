<?php
/**
 * Gravity Wiz // Gravity Forms // Capture List Field Column as Comma-delimited List
 * https://gravitywiz.com/
 */
// Update "123" to your form ID.
add_action( 'gform_pre_submission_905', function( $form ) {

	$list_field_id   = 1; // Update to your List field ID.
	$column_number   = 3; // Update to the column number you would like to fetch values from.
	$target_field_id = 2; // Update to the ID of the field to which you would like to capture values.

	$list_field = GFAPI::get_field( $form, $list_field_id );
	$rows       = $list_field->create_list_array( $_POST["input_{$list_field_id}"] );
	$values     = array();

	foreach ( $rows as $row ) {
		$row = array_values( $row );
		$values[] = $row[ $column_number - 1 ];
	}

	$_POST[ "input_{$target_field_id}" ] = implode( ',', $values );
	
} );
