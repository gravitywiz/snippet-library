<?php
/**
 * Gravity Perks // Nested Forms // Only show child field labels when Nested Form field has no label
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gpnf_export_child_field_header', function( $header, $form, $field, $child_field ) {

    $parent_label = $field->get_field_label( false, null );

    if ( empty( $parent_label ) ) {
	
        $header = $child_field->get_field_label( false, null );

    } else {

        $header = sprintf( '%s / %s', $parent_label, $child_field->get_field_label( false, null ) );
	}

	return $header;
}, 10, 4 );
