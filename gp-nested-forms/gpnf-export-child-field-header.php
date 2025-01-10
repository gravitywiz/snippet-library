<?php
/**
 * Gravity Perks // Nested Forms // Only show child field labels when Nested Form field has no label
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gpnf_export_child_field_header', function( $header, $form, $field, $child_field, $input ) {

	$parent_label = $field->get_field_label( false, null );
	if ( ! empty( $parent_label ) ) {
		return $header;
	}

	$child_field_label = $child_field->get_field_label( false, null );

	if ( rgar( $input, 'label' ) ) {
		$header = sprintf( '%s (%s)', $child_field_label, $input['label'] );
	} else {
		$header = $child_field_label;
	}

	return $header;
}, 10, 5 );
