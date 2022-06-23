<?php
/**
 * Gravity Perks // Nested Forms // Filter Child Form to Only Show Summary Fields
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gform_pre_render', 'gpnf_filter_child_form_by_summary_fields' );
add_filter( 'gform_pre_process', 'gpnf_filter_child_form_by_summary_fields' );
function gpnf_filter_child_form_by_summary_fields( $form ) {

	if ( ! is_callable( 'gp_nested_forms' ) ) {
		return $form;
	}

	$parent_form_id = rgar( $_REQUEST, 'gpnf_parent_form_id' );
	if ( ! $parent_form_id ) {
		return $form;
	}

	$nested_form_field = GFAPI::get_field( GFAPI::get_form( $parent_form_id ), rgar( $_REQUEST, 'gpnf_nested_form_field_id' ) );
	if ( $form['id'] != $nested_form_field->gpnfForm ) {
		return $form;
	}

	$filtered_fields = array();
	foreach ( $form['fields'] as $field ) {
		if ( in_array( $field->id, $nested_form_field->gpnfFields ) ) {
			$filtered_fields[] = $field;
		}
	}

	$form['fields'] = $filtered_fields;

	return $form;
}
