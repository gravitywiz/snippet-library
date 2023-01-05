<?php
/**
 * Gravity Forms // Gravity Flow // Force Read Only Fields to not be editable on User Input Step
 * https://gravitywiz.com/
 */
add_filter( 'gravityflow_editable_fields_user_input', function( $editable_fields, $step ) {
	foreach ( $editable_fields as $key => $potential ) {
		$field = GFFormsModel::get_field( $step->get_form_id(), $potential );
		//Use cases may also want to evaluate $field['isRequired']
		if ( $field['gwreadonly_enable'] ) {
			unset( $editable_fields[ $key ] );
		}
	}

	return $editable_fields;
}, 10, 2 );
